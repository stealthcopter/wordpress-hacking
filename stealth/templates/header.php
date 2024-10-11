<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress Tools</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Prism.js CSS for code highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet"/>

    <style>
        /* Toast container in bottom-right corner */
        .toast-container {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            z-index: 1055; /* Above other content */
        }
        .toast-header-success {
            background-color: #28a745; /* Bootstrap success color */
            color: white;
        }
        .toast-header-error {
            background-color: #dc3545; /* Bootstrap danger color */
            color: white;
        }
        .toast-header-info {
            background-color: #17a2b8; /* Bootstrap info color */
            color: white;
        }
    </style>

    <script>
        // Function to create and show a toast
        function showToast(message, title = 'Notification', type = 'info') {
            let toastClass;
            switch (type) {
                case 'success':
                    toastClass = 'toast-header-success';
                    break;
                case 'error':
                    toastClass = 'toast-header-error';
                    break;
                case 'info':
                default:
                    toastClass = 'toast-header-info';
                    break;
            }

            // Create the toast element
            const toastHTML = `
        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true">
          <div class="toast-header ${toastClass}">
            <strong class="me-auto">${title}</strong>
            <small class="text-muted">just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">
            ${message}
          </div>
        </div>
      `;

            const toastElement = document.createElement('div');
            toastElement.innerHTML = toastHTML;
            document.getElementById('toast-container').appendChild(toastElement);

            // Get the newly created toast element and show it
            const toast = new bootstrap.Toast(toastElement.querySelector('.toast'));
            toast.show();
        }

        function showSuccess(message, title = 'Success') {
            showToast(message, title, 'success');
        }

        function showError(message, title = 'Error') {
            showToast(message, title, 'error');
        }

        function showInfo(message, title = 'Info') {
            showToast(message, title, 'info');
        }

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Function to handle copying text
            function copyToClipboard(text) {
                // Use modern Clipboard API if available
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        showSuccess('Copied to clipboard');
                    }).catch(err => {
                        console.error('Failed to copy: ', err);
                    });
                } else {
                    // Fallback method using a hidden textarea
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.style.position = 'fixed';  // Prevent scrolling to the bottom
                    document.body.appendChild(textarea);
                    textarea.focus();
                    textarea.select();

                    try {
                        document.execCommand('copy');
                        showSuccess('Copied to clipboard');
                    } catch (err) {
                        console.error('Fallback: Oops, unable to copy', err);
                    }

                    document.body.removeChild(textarea);
                }
            }

            // Find all spans with the 'copy-text' class and make them copyable
            document.querySelectorAll('.copy-text').forEach(span => {
                // Create the clipboard icon
                const icon = document.createElement('button');
                icon.innerHTML = '📋'; // Clipboard icon
                icon.classList.add('copy-btn');
                icon.title = 'Copy to clipboard'; // Tooltip for accessibility

                // Append the clipboard icon next to the text
                span.appendChild(icon);

                // Add event listener for the icon click
                icon.addEventListener('click', () => {
                    const textToCopy = span.innerText.replace('📋', '').trim(); // Remove icon from the text
                    copyToClipboard(textToCopy);
                });
            });
        });
    </script>

    <style>
        .copy-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.5;
        }

        .copy-btn:hover {
            color: green;
        }
    </style>

</head>
<body class="bg-dark">
<div id="toast-container" class="toast-container"></div>
<?php require_once 'navbar.php' ?>
<div class="container">
    <div class="">

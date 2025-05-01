<?php
function showSweetAlert($type, $title, $message) {
    $icon = '';
    switch ($type) {
        case 'error':
            $icon = 'error';
            break;
        case 'info':
            $icon = 'info';
            break;
        case 'success':
            $icon = 'success';
            break;
    }

    echo "<script>
        Swal.fire({
            icon: '$icon',
            title: '$title',
            html: '$message',
            confirmButtonText: 'Close',
            customClass: {
                confirmButton: 'bg-red-600 text-white'
            }
        });
    </script>";
}
?>

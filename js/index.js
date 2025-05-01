// js/sweetalert.js
function showSweetAlert(type, title, message) {
    let icon = '';
    switch (type) {
        case 'error':
            icon = 'error';
            break;
        case 'info':
            icon = 'info';
            break;
        case 'success':
            icon = 'success';
            break;
    }

    Swal.fire({
        icon: icon,
        title: title,
        html: message,
        confirmButtonText: 'Close',
        customClass: {
            confirmButton: 'bg-red-600 text-white'
        }
    });
}

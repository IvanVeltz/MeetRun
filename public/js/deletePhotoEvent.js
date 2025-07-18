document.querySelectorAll('.delete-photo-btn').forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault();

        if (!confirm('Supprimer cette photo ?')) return;

        const photoId = this.dataset.photoId;
        const csrfToken = this.dataset.csrf;

        fetch(`/photo/delete/${photoId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `_token=${csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Supprimer la photo du DOM
                this.closest('.photo-wrapper').remove();
            } else {
                alert(data.message || 'Erreur lors de la suppression.');
            }
        })
        .catch(() => alert('Erreur AJAX'));
    });
});
const deleteAction = (elements, callback) => {
    elements.forEach(button => {
        button.addEventListener('click', () => {
            callback(button);
        });
    });
}

deleteAction(document.querySelectorAll('.delete'), (button) => {
    console.log('Button clicked:', button.dataset.ticketId);
    fetch('/ticket/' + button.dataset.ticketId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': 'your-csrf-token', // Si vous utilisez un token CSRF
            'Content-Type': 'application/json'
        }
    })
        .then(response => {
            console.log('Delete response status:', response.status);
            if (response.ok) {
                console.log('Ticket successfully deleted');
                // Supprimer la ligne du ticket
                document.getElementById('ticket-' + button.dataset.ticketId).remove();

                // Mettre à jour le total en AJAX
                fetch('/update-total', {
                    method: 'PATCH',
                })
                    .then(response => response.text())
                    .then(total => {
                        // Mettez à jour l'affichage du total sur la page
                        document.getElementById('total').innerText = total + ' €';
                        console.log('Nouveau total :', total);
                    })
                    .catch(error => {
                        console.error('Il y a eu un problème avec l\'opération fetch :', error);
                    });
            } else {
                response.json().then(data => {
                    console.error('Erreur lors de la suppression du ticket:', data.error);
                });
            }
        })
        .catch(error => {
            console.error('Il y a eu un problème avec l\'opération fetch pour supprimer le ticket :', error);
        });
});

const deleteAction = (elements, callback) => {
    elements.forEach(button => {
        button.addEventListener('click', () => {
            callback(button);
        });
    });
}

deleteAction(document.querySelectorAll('.delete'), (button) => {
    fetch('/ticket/' + button.dataset.ticketId, {
        method: 'DELETE',
    })
        .then(response => {
            if (response.ok) {
                // Supprimer la ligne du ticket
                document.getElementById('ticket-' + button.dataset.ticketId).remove();
                button.remove();

                // Mettre à jour le total en AJAX
                fetch('/update-total', {
                    method: 'PATCH',
                })
                    .then(response => response.text())
                    .then(total => {
                        // Mettez à jour l'affichage du total sur la page
                        document.getElementById('total').innerText = 'Différence: ' + total;
                        console.log('Nouveau total :', total);
                    })
                    .catch(error => {
                        console.error('Il y a eu un problème avec l\'opération fetch :', error);
                    });
            } else {
                console.error('Il y a eu un problème avec la suppression du ticket.');
            }
        })
        .catch(error => {
            console.error('Il y a eu un problème avec l\'opération fetch pour supprimer le ticket :', error);
        });
});

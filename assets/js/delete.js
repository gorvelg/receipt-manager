document.addEventListener('DOMContentLoaded', () => {
    const ticketItems = document.querySelectorAll('.ticket-item');

    ticketItems.forEach(ticket => {
        let startX;
        const content = ticket.querySelector('.ticket-content');
        const deleteButton = ticket.querySelector('.delete-button');

        // Détecter le début du glissement
        content.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });

        // Détecter le mouvement
        content.addEventListener('touchmove', (e) => {
            const moveX = e.touches[0].clientX;
            const diff = moveX - startX;

            if (diff < -50) { // Glissement vers la gauche
                ticket.classList.add('swiped');
            } else if (diff > 50) { // Glissement vers la droite
                ticket.classList.remove('swiped');
            }
        });

        // Suppression après clic sur le bouton
        deleteButton.addEventListener('click', () => {
            const ticketId = deleteButton.dataset.ticketId;

            fetch(`/ticket/${ticketId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
                .then(response => {
                    if (response.ok) {
                        ticket.remove();
                        updateTotal();
                    } else {
                        console.error('Erreur lors de la suppression du ticket.');
                    }
                })
                .catch(error => {
                    console.error('Erreur réseau lors de la suppression du ticket :', error);
                });
        });
    });

    // Mettre à jour le total après suppression
    function updateTotal() {
        fetch('/update-total', { method: 'PATCH' })
            .then(response => response.text())
            .then(total => {
                document.getElementById('total').innerText = `${total} €`;
            })
            .catch(error => {
                console.error('Erreur réseau lors de la mise à jour du total :', error);
            });
    }
});

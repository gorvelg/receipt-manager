document.addEventListener('DOMContentLoaded', () => {
    const ticketItems = document.querySelectorAll('.tag');

    ticketItems.forEach(item => {
        let startX;
        const content = item.querySelector('.delete-slide');
        const deleteButton = item.querySelector('.delete-button');

        // Détecter le début du glissement
        content.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });

        // Détecter le mouvement du doigt
        content.addEventListener('touchmove', (e) => {
            const moveX = e.touches[0].clientX;
            const diff = moveX - startX;

            if (diff < -50) { // Glissement vers la gauche
                item.classList.add('swiped');
                deleteButton.style.transform = 'translateX(0)';
            } else if (diff > 50) { // Glissement vers la droite
                item.classList.remove('swiped');
                deleteButton.style.transform = 'translateX(100%)';
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
                        // Supprime visuellement le ticket
                        item.remove();
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

    // Fonction pour mettre à jour le total après suppression
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

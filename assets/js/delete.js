const deleteButton = document.querySelectorAll('.delete');

deleteButton.forEach(button => {
    button.addEventListener('click', () => {
        fetch('/ticket/' + button.dataset.ticketId, {
            method: 'DELETE',
        });
        document.getElementById('ticket-' + button.dataset.ticketId).remove();
        button.remove();
    });
});



const deleteAction = (elements, callback) => {
    elements.forEach(button => {
        button.addEventListener('click', () => {
            callback(button)
        });
    });
}

deleteAction(document.querySelectorAll('.delete'), (button) => {
    fetch('/ticket/' + button.dataset.ticketId, {
        method: 'DELETE',
    });
    document.getElementById('ticket-' + button.dataset.ticketId).remove();
    button.remove();
})


$(function () {
  $(document).on('click', 'a#delete, button#delete', function (e) {
    e.preventDefault();
    var link = $(this).attr('href');
    var deleteText = $(this).data('delete-text') || 'this data';

    // Set delete link to modal button
    $('#confirmDeleteBtn').attr('href', link);

    // Update modal message if custom text provided
    if ($(this).data('delete-text')) {
      $('#deleteConfirmModal .modal-body p:first').text('Are you sure you want to delete ' + deleteText + '?');
    } else {
      $('#deleteConfirmModal .modal-body p:first').text('Are you sure you want to delete this data?');
    }

    // Show modal
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
  });

  // Handle confirm delete button click
  $(document).on('click', '#confirmDeleteBtn', function (e) {
    var link = $(this).attr('href');
    if (link) {
      window.location.href = link;
    }
  });
});

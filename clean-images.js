jQuery(document).ready(function($) {
    $('#select-all').click(function() {
        $('input[type="checkbox"]').prop('checked', this.checked);
    });

    $('#clean-images-form').on('click', '.start-delete', function(e) {
        e.preventDefault();
        
        var $form = $(this).closest('form');
        var $progressBar = $('.progress');
        var imageIds = [];
        
        $form.find('input[name="image_ids[]"]:checked').each(function() {
            imageIds.push($(this).val());
        });

        if (imageIds.length === 0) {
            alert('Please select images to delete.');
            return;
        }

        if (!confirm('Are you sure you want to delete ' + imageIds.length + ' images?')) {
            return;
        }

        var $progressContainer = $('.progress-bar').show();
        $progressBar.css('width', '0%');

        var batchSize = 5;
        var total = imageIds.length;
        var processed = 0;
        var errors = [];

        function processBatch() {
            var currentBatch = imageIds.splice(0, batchSize);
            
            $.ajax({
                url: cleanImagesVars.ajaxurl,
                method: 'POST',
                data: {
                    action: 'clean_images_delete',
                    nonce: cleanImagesVars.nonce,
                    image_ids: currentBatch
                },
                success: function(response) {
                    processed += currentBatch.length;
                    var percent = Math.round((processed / total) * 100);
                    $progressBar.css('width', percent + '%');

                    if (response.data.errors.length > 0) {
                        errors = errors.concat(response.data.errors);
                    }

                    if (imageIds.length > 0) {
                        processBatch();
                    } else {
                        var message = 'Deleted ' + response.data.deleted + ' of ' + total + ' images.';
                        if (errors.length > 0) {
                            message += ' Failed to delete ' + errors.length + ' images.';
                        }
                        alert(message);
                        location.reload();
                    }
                }
            });
        }

        processBatch();
    });
});
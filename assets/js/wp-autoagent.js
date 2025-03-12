jQuery(document).ready(function($) {
    
    // Listen for clicks on the menu items
    $('#wpaa_setting_menu li').on('click', function() {
        var page = $(this).data('page');

        // Hide all pages first
        $('#wpaa_settings_content .wpaa-page').hide();

        // Show the selected page
        $('#wpaa_' + page).show();

        // Remove 'active' class from all menu items and add to the clicked one
        $('#wpaa_setting_menu li').removeClass('active');
        $(this).addClass('active');
    });

    // Trigger click on the first item to show it by default (Upload Article)
    $('#wpaa_setting_menu li:first').trigger('click');

    $('#wpaa_upload_article_form').submit(function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'wpaa_article_upload');
        formData.append('nonce', wpaa_nonce.nonce);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert(response.data);
            },
            error: function(response) {
                alert(response.data);
            }
        });
    });


});

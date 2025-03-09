jQuery(document).ready(function($) {
    
    // Listen for clicks on the menu items
    $('#sc_settings_menu li').on('click', function() {
        var page = $(this).data('page');

        // Hide all pages first
        $('#sc_settings_content .sc-page').hide();

        // Show the selected page
        $('#sc_' + page).show();

        // Remove 'active' class from all menu items and add to the clicked one
        $('#sc_settings_menu li').removeClass('active');
        $(this).addClass('active');
    });

    // Trigger click on the first item to show it by default (Upload Article)
    $('#sc_settings_menu li:first').trigger('click');

    $('#sc_upload_article_form').submit(function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'wp_autoagent_settings_upload');
        formData.append('nonce', wp_autoagent_settings.nonce);

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

    $('#sc_submit_article').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'api_proxy_request',
                nonce: wp_autoagent_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Response from WP AutoAgent API:', response.data);
                    alert('API call to WP AutoAgent successful. Check console for details.');
                } else {
                    console.error('Error:', response.data);
                    alert('Failed to call WP AutoAgent API: ' + response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                alert('Failed to make AJAX request: ' + textStatus);
            }
        });
    });

});

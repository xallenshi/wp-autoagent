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


    // Load agent scope when agent is selected
    $('select[name="agent_id"]').on('change', function() {
        var agent_id = $(this).val();
        
        // Clear previous selections
        $('select[name="selected_pages[]"] option').prop('selected', false);
        $('select[name="selected_admin_pages[]"] option').prop('selected', false);
        
        if (agent_id) {
            // Request agent scope via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_agent_scope',
                    nonce: wpaa_setting_nonce.nonce,
                    agent_id: agent_id
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Select the pages from the response
                        var scope = response.data;
                        
                        // Loop through scope array and select matching options
                        $.each(scope, function(index, value) {
                            // Check if it's an admin page (containing .php)
                            if (String(value).indexOf('.php') !== -1) {
                                $('select[name="selected_admin_pages[]"] option[value="' + value + '"]').prop('selected', true);
                            } else {
                                // It's a page ID
                                $('select[name="selected_pages[]"] option[value="' + value + '"]').prop('selected', true);
                            }
                        });
                    }
                },
                error: function() {
                    loadingMsg.remove();
                    $('.wpaa-plugin-container form').prepend('<div class="error"><p>Error loading agent scope.</p></div>');
                }
            });
        }
    });


    // Custom multi-select functionality for the publish page
    $(document).on('mousedown', '.wpaa-publish-scope-container select[multiple] option', function(e) {
        let isSelected = $(this).prop('selected');
        
        $(document).one('mouseup', () => {
            $(this).prop('selected', !isSelected);
            $(this).parent().focus();
        });
        
        return false;
    });


    // Upload Article
    $('#wpaa_upload_article_form').submit(function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'wpaa_article_upload');
        formData.append('nonce', wpaa_setting_nonce.nonce);

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


    // Create Agent
    $('#wpaa_create_agent_form').submit(function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'wpaa_create_agent');
        formData.append('nonce', wpaa_setting_nonce.nonce);

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


    // Knowledge Base Link
    $(document).on('click', '.wpaa-kb-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        $('#wpaa_setting_menu li[data-page="' + page + '"]').trigger('click');
    });


    // Agent List
    $(document).on('click', '.wpaa-agent-list .agent-item', function(e) {
        e.preventDefault();
        var agent_id = $(this).data('agent_id');
        
        // If clicking "New Agent", just open create page with blank form
        if (agent_id === 'new') {
            $('#wpaa_setting_menu li[data-page="create"]').trigger('click');
            // Clear form fields
            $('#name').val('');
            $('#instructions').val('');
            $('#model').val('gpt-4o'); // Set default model
            // Uncheck all articles
            $('input[name="articles[]"]').prop('checked', false);
            // Set page title for new agent
            $('.wpaa-plugin-container h1').text('Create Your AI Agent');
            $('#wpaa_create_agent_button').text('Create AI Assistant');
            return;
        }

        // Otherwise load existing agent
        $('#wpaa_setting_menu li[data-page="create"]').trigger('click');

        // Load agent info from database
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpaa_get_agent',
                nonce: wpaa_setting_nonce.nonce,
                agent_id: agent_id
            },
            success: function(response) {
                if (response.success) {
                    // Fill form with agent data
                    $('.wpaa-plugin-container h1').text('Update Your AI Agent');
                    $('#wpaa_create_agent_button').text('Update AI Assistant');

                    $('#name').val(response.data.name);
                    $('#instructions').val(response.data.instructions);
                    $('#model').val(response.data.model);
                    
                    // Check selected articles
                    $('input[name="articles[]"]').prop('checked', false);
                    if (response.data.article_ids) {
                        var articleIds = JSON.parse(response.data.article_ids);
                        articleIds.forEach(function(article_id) {
                            $('#article_' + article_id).prop('checked', true);
                        });
                    }
                } else {
                    alert('Error loading agent data');
                }
            },
            error: function() {
                alert('Error loading agent data');
            }
        });
    });






});

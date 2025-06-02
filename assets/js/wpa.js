jQuery(document).ready(function($) {
    
    // Listen for clicks on the menu items
    $('#wpa_setting_menu li').on('click', function() {
        var page = $(this).data('page');

        // Hide all pages first
        $('#wpa_settings_content .wpa-page').hide();

        // Show the selected page
        $('#wpa_' + page).show();

        // Remove 'active' class from all menu items and add to the clicked one
        $('#wpa_setting_menu li').removeClass('active');
        $(this).addClass('active');
    });

    // Listen for clicks on the agent list
    $('.wpa-agent-list1 li, .wpa-agent-list2 li').on('click', function() {
        // Remove 'active' class from all agents and add to the clicked one
        $('.wpa-agent-list1 li, .wpa-agent-list2 li').removeClass('active');
        $(this).addClass('active');
    });


    // Trigger click on the first item to show it by default (Upload Article)
    $('#wpa_setting_menu li:first').trigger('click');

    // Load agent scope when agent is selected
    $('select[name="agent_id"]').on('change', function() {
        var agent_id = $(this).val();
        
        // Clear previous selections
        $('select[name="selected_pages[]"] option').prop('selected', false);
        $('select[name="selected_admin_pages[]"] option').prop('selected', false);
        
        if (agent_id) {

            $('select[name="selected_pages[]"], select[name="selected_admin_pages[]"]').prop('disabled', false);
            
            // Request agent scope via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpa_get_agent_scope',
                    nonce: wpa_setting_nonce.nonce,
                    agent_id: agent_id
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Select the pages from the response
                        var scope = response.data;
                        
                        // Loop through scope array and select matching options
                        $.each(scope, function(index, value) {
                            // Check if it's an admin page (containing .php)
                            if (isNaN(value)) {
                                $('select[name="selected_admin_pages[]"] option[value="' + value + '"]').prop('selected', true);
                            } else {
                                // It's a page ID, frontend page
                                $('select[name="selected_pages[]"] option[value="' + value + '"]').prop('selected', true);
                            }
                        });
                    }
                },
                error: function() {
                    $('.wpa-plugin-container form').prepend('<div class="error"><p>Error loading agent scope.</p></div>');
                }
            });
        }
    });


    // Custom multi-select functionality for the publish page
    $(document).on('mousedown', 'select[name="selected_pages[]"] option, select[name="selected_admin_pages[]"] option', function(e) {
        e.preventDefault();

        var $option = $(this);
        var $select = $option.parent();
        var scroll = $select.scrollTop();

        // Toggle selection
        $option.prop('selected', !$option.prop('selected'));

        // Blur and refocus to prevent jump
        $select.blur();
        setTimeout(function() {
            $select.focus();
            $select.scrollTop(scroll);
        }, 1);

        return false;
    });


    // Create Agent
    $('#wpa_create_agent_form').submit(function(event) {
        event.preventDefault();

        $('#wpa_create_agent_button').prop('disabled', true);
        button_text = $('#wpa_create_agent_button').text();
        if (button_text === 'Create AI Agent') {
            $('#wpa_create_agent_button').text('Creating...');
        } else {
            $('#wpa_create_agent_button').text('Updating...');
        }

        const checkboxes = document.querySelectorAll('input[name="articles[]"]');
        let checkedOne = false;
        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                checkedOne = true;
            }
        });
        if (!checkedOne) {
            showNotification('Please select at least one knowledge article.', 'error');
            setTimeout(function() {
                $('#wpa_create_agent_button').prop('disabled', false);
                $('#wpa_create_agent_button').text(button_text);
            }, 2000);
            return;
        }

        var formData = new FormData(this);
        formData.append('action', 'wpa_create_agent');
        formData.append('nonce', wpa_setting_nonce.nonce);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    setTimeout(function() {
                        showNotification(response.data, 'success');
                    }, 1000);
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification(response.data, 'error');
                }
            },
            error: function(response) {
                showNotification(response.data, 'error');
            }
        });

        setTimeout(function() {
            $('#wpa_create_agent_button').prop('disabled', false);
            $('#wpa_create_agent_button').text(button_text);
        }, 1000);

    });

    // Knowledge Base Link
    $(document).on('click', '.wpa-kb-link', function(e) {
        e.preventDefault();
        // Modal HTML
        var modalHtml = `
            <div class="wpa-modal-overlay">
                <div class="wpa-modal">
                    <button class="wpa-modal-close" title="Close">&times;</button>
                    <div class="wpa-modal-header">
                        <h2>Upload Knowledge Article</h2>
                    </div>
                    <hr class="wpa-hr">
                    <div class="wpa-modal-content">
                        <div class="wpa-modal-description">
                            <h4>Upload your knowledge articles to enrich your Agent's contextual understanding, enabling it to deliver more accurate and intelligent responses.</h4>
                        </div>
                        <form id="wpa_upload_article_form" method="post" enctype="multipart/form-data">
                            <div class="wpa-file-upload">
                                <input type="file" name="article_file" id="article_file" accept=".txt,.doc,.docx,.pdf,.pptx,.md,.html,.json" required>
                                <button type="submit" class="wpa-upload-btn" id="wpa_upload_article_button">Upload</button>
                            </div>
                        </form>
                        <hr>
                        <div class="wpa-modal-footer">
                            <p><b>Suggested Content:</b> Product catalogs, manuals, user guides, and technical documentation
                            </br><b>Supported Formats:</b> .txt, .doc, .docx, .pdf, .pptx, .md, .html, .json</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        // Remove any existing modal
        $('.wpa-modal-overlay').remove();
        // Append modal to body
        $('body').append(modalHtml);

    });

    // Handle upload in modal
    $(document).on('submit', '#wpa_upload_article_form', function(event) {
        event.preventDefault();

        $('#wpa_upload_article_button').prop('disabled', true);
        $('#wpa_upload_article_button').text('Uploading...');

        var formData = new FormData(this);
        formData.append('action', 'wpa_article_upload');
        formData.append('nonce', wpa_setting_nonce.nonce);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification(response.data, 'success');
                    location.reload();
                } else {
                    showNotification(response.data, 'error');
                }
            },
            error: function(response) {
                showNotification(response.data, 'error');
            },
            complete: function() {
                $('#wpa_upload_article_button').prop('disabled', false);
                $('#wpa_upload_article_button').text('Upload');
            }
        });
    });


    // Agent List on Create Page
    $(document).on('click', '.wpa-agent-list1 li', function(e) {
        // If the click was on a child <a>, prevent default
        if ($(e.target).is('a')) {
            e.preventDefault();
        }
        var agent_id = $(this).find('a').data('agent_id');
        
        // If clicking "New Agent", just open create page with blank form
        if (agent_id === 'new') {
            $('#wpa_setting_menu li[data-page="create"]').trigger('click');
            // Clear form fields
            $('#agent_id').val('');
            $('#name').val('');
            $('#instructions').val('');
            $('#greeting_message').val('');
            $('#model').val('gpt-4o'); // Set default model
            // Uncheck all articles and functions
            $('input[name="articles[]"]').prop('checked', false);
            $('input[name="functions[]"]').prop('checked', false);
            // Set page title for new agent
            $('#wpa_create_agent_title').text('Create Your Agent');
            $('#wpa_create_agent_button').text('Create Agent');
            $('#delete_agent_link').hide();
            return;
        }

        //disable the agent from first
        $('#wpa_create_agent_form').find('input, textarea, select').prop('disabled', true);
        // Load agent info from database
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpa_get_agent',
                nonce: wpa_setting_nonce.nonce,
                agent_id: agent_id
            },
            success: function(response) {
                if (response.success) {
                    // Fill form with agent data
                    $('#wpa_create_agent_title').text('Update Your Agent');
                    $('#wpa_create_agent_button').text('Update Agent');

                    $('#agent_id').val(response.data.id);
                    $('#name').val(response.data.name);
                    $('#instructions').val(response.data.instructions);
                    $('#greeting_message').val(response.data.greeting_message);
                    $('#model').val(response.data.model);
                    $('#delete_agent_link').show();
                    $('#delete_agent_link').data('agent_id', response.data.id);
                    
                    // Check selected articles
                    $('input[name="articles[]"]').prop('checked', false);
                    if (response.data.article_ids) {
                        var articleIds = JSON.parse(response.data.article_ids);
                        articleIds.forEach(function(article_id) {
                            $('#article_' + article_id).prop('checked', true);
                        });
                    }

                    // Check selected functions
                    $('input[name="functions[]"]').prop('checked', false);
                    if (response.data.function_ids) {
                        var functionIds = JSON.parse(response.data.function_ids);
                        functionIds.forEach(function(function_id) {
                            $('#function_' + function_id).prop('checked', true);
                        });
                    }

                } else {
                    showNotification('Error loading agent data', 'error');
                }
            },
            error: function() {
                showNotification('Error loading agent data', 'error');
            },
            complete: function() {
                $('#wpa_create_agent_form').find('input, textarea, select').prop('disabled', false);
            }
        });
    });


    // Agent List on Publish Page
    $(document).on('click', '.wpa-agent-list2 li', function(e) {
        // If the click was on a child <a>, prevent default
        if ($(e.target).is('a')) {
            e.preventDefault();
        }
        var agent_id = $(this).find('a').data('agent_id');
        
        // Update selected state in the list
        $('.wpa-agent-list2 li').removeClass('active');
        $(this).addClass('active');
        
        // Update the select and trigger change
        $('select[name="agent_id"]').val(agent_id).trigger('change');
    });


    // Publish Agent
    $('#wpa-publish-agent-form').on('submit', function(e) {
        e.preventDefault();

        $('#wpa_publish_agent_button').prop('disabled', true);
        $('#wpa_publish_agent_button').text('Publishing...');

        var formData = new FormData(this);
        formData.append('action', 'wpa_publish_agent');
        formData.append('nonce', wpa_setting_nonce.nonce);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification(response.data, 'success');
                } else {
                    showNotification('Error: ' + response.data, 'error');
                }
                $('#wpa_publish_agent_button').prop('disabled', false);
                $('#wpa_publish_agent_button').text('Publish Agent');
            },
            error: function(xhr) {
                showNotification('Error publishing agent. Please try again.', 'error');
                $('#wpa_publish_agent_button').prop('disabled', false);
                $('#wpa_publish_agent_button').text('Publish Agent');
            }
        });

    });
    

    
    // Add notification container if not present
    if ($('#wpa-notification-container').length === 0) {
        $('body').append('<div id="wpa-notification-container"></div>');
    }

    // Modern notification function
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="wpa-notification wpa-notification-${type}">
                ${message}
            </div>
        `);
        $('#wpa-notification-container').append(notification);
        setTimeout(() => {
            notification.fadeOut(400, function() { $(this).remove(); });
        }, 2000);
    }

    // Delete Agent Confirmation Popup
    $(document).on('click', '.wpa-delete-agent-link', function(e) {
        e.preventDefault();
        var agent_id = $(this).data('agent_id');
        
        // Remove any existing modal
        $('.wpa-modal-overlay').remove();
        
        // Modal HTML
        var modalHtml = `
            <div class="wpa-modal-overlay">
                <div class="wpa-modal">
                    <button class="wpa-modal-close" title="Close">&times;</button>
                    <div class="wpa-modal-header">
                        <h3>Confirm Deletion</h3>
                    </div>
                    <hr class="wpa-hr">
                    <div class="wpa-modal-content">
                        <p>Are you sure you want to delete this agent?</p>
                        <div class="wpa-modal-button">
                            <button id="wpa-confirm-delete" data-agent_id="${agent_id}">Delete</button>
                            <button id="wpa-confirm-cancel">Cancel</button>
                        </div>
                    </div>
                    <hr>
                    <div class="wpa-modal-footer">
                        <p>The assigned article and its functions will remain intact, but the conversation history with this agent will be deleted.</p>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modalHtml);
    });

    // Handle confirm delete
    $(document).on('click', '#wpa-confirm-delete', function(e) {
        e.preventDefault();
        var agent_id = $(this).data('agent_id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpa_delete_agent',
                nonce: wpa_setting_nonce.nonce,
                agent_id: agent_id
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.data, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification(response.data, 'error');
                }
            },
            error: function() {
                showNotification('Error deleting agent. Please try again.', 'error');
            }
        });
        $('.wpa-modal-overlay').remove();
    });


    // Close modal on click
    $(document).on('click', '.wpa-modal-close, .wpa-modal-overlay, #wpa-confirm-cancel', function(e) {
        if ($(e.target).hasClass('wpa-modal-close') || $(e.target).hasClass('wpa-modal-overlay') || $(e.target).is('#wpa-confirm-cancel')) {
            $('.wpa-modal-overlay').remove();
        }
    });


    // Save Access Key
    $('#wpa_save_key_button').on('click', function(e) {
        e.preventDefault();
        $('#wpa_save_key_button').prop('disabled', true);
        $('#wpa_save_key_button').text('Saving...');

        var access_key = $('#access_key').val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpa_save_key',
                nonce: wpa_setting_nonce.nonce,
                access_key: access_key
            },
            success: function(response) {
                if (response.success) {
                    setTimeout(() => {
                        showNotification(response.data, 'success');
                    }, 1000);
                } else {
                    showNotification(response.data, 'error');
                }
            },
            error: function(xhr) {
                showNotification('Error saving access key. Please try again.', 'error');
            }
        });

        setTimeout(() => {
            $('#wpa_save_key_button').prop('disabled', false);
            $('#wpa_save_key_button').text('Save Key');
        }, 2000);
    });






    

});



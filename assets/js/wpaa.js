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

    // Listen for clicks on the agent list
    $('.wpaa-agent-list1 li, .wpaa-agent-list2 li').on('click', function() {
        // Remove 'active' class from all agents and add to the clicked one
        $('.wpaa-agent-list1 li, .wpaa-agent-list2 li').removeClass('active');
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
                    action: 'wpaa_get_agent_scope',
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


    // Create Agent
    $('#wpaa_create_agent_form').submit(function(event) {
        event.preventDefault();

        $('#wpaa_create_agent_button').prop('disabled', true);
        button_text = $('#wpaa_create_agent_button').text();
        if (button_text === 'Create AI Agent') {
            $('#wpaa_create_agent_button').text('Creating...');
        } else {
            $('#wpaa_create_agent_button').text('Updating...');
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
                $('#wpaa_create_agent_button').prop('disabled', false);
                $('#wpaa_create_agent_button').text(button_text);
            }, 2000);
            return;
        }

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
            $('#wpaa_create_agent_button').prop('disabled', false);
            $('#wpaa_create_agent_button').text(button_text);
        }, 1000);

    });

    // Knowledge Base Link
    $(document).on('click', '.wpaa-kb-link', function(e) {
        e.preventDefault();
        // Modal HTML
        var modalHtml = `
            <div class="wpaa-modal-overlay">
                <div class="wpaa-modal">
                    <button class="wpaa-modal-close" title="Close">&times;</button>
                    <div class="wpaa-modal-header">
                        <h2>Knowledge Base Upload</h2>
                        <p class="wpaa-modal-subtitle">Enhance your AI Agent with custom knowledge</p>
                    </div>
                    <hr>
                    <div class="wpaa-modal-content">
                        <div class="wpaa-modal-description">
                            <p>Upload articles to the knowledge base to improve your AI Agent's contextual understanding.</p>
                            <p>Supported content: Product catalogs, manuals, guides, documentation</p>
                        </div>
                        <form id="wpaa_upload_article_form" method="post" enctype="multipart/form-data">
                            <div class="wpaa-file-upload">
                                <input type="file" name="article_file" id="article_file" accept=".txt,.doc,.docx,.xls,.xlsx,.pdf" required>
                                <label for="article_file" class="wpaa-file-label">
                                    <span class="wpaa-file-icon">📄</span>
                                    <span>Choose a file</span>
                                </label>
                            </div>
                            <button type="submit" class="wpaa-upload-btn">Upload Article</button>
                        </form>
                        <div id="wpaa_article_list" class="wpaa-article-list"></div>
                        <hr>
                        <div class="wpaa-modal-footer">
                            <p>Supported content: Product catalogs, manuals, guides, documentation</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        // Remove any existing modal
        $('.wpaa-modal-overlay').remove();
        // Append modal to body
        $('body').append(modalHtml);

        // Load article list via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpaa_get_article_list',
                nonce: wpaa_setting_nonce.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('#wpaa_article_list').html(response.data);
                } else {
                    $('#wpaa_article_list').html('<div>No articles found.</div>');
                }
            },
            error: function() {
                $('#wpaa_article_list').html('<div>Error loading articles.</div>');
            }
        });
    });

    // Handle upload in modal
    $(document).on('submit', '#wpaa_upload_article_form', function(event) {
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
                if (response.success) {
                    showNotification(response.data, 'success');
                } else {
                    showNotification(response.data, 'error');
                }
                // Reload article list after upload
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpaa_get_article_list',
                        nonce: wpaa_setting_nonce.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            $('#wpaa_article_list').html(response.data);
                        }
                    }
                });
            },
            error: function(response) {
                showNotification(response.data, 'error');
            }
        });
    });


    // Agent List on Create Page
    $(document).on('click', '.wpaa-agent-list1 li', function(e) {
        // If the click was on a child <a>, prevent default
        if ($(e.target).is('a')) {
            e.preventDefault();
        }
        var agent_id = $(this).find('a').data('agent_id');
        
        // If clicking "New Agent", just open create page with blank form
        if (agent_id === 'new') {
            $('#wpaa_setting_menu li[data-page="create"]').trigger('click');
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
            $('.wpaa-plugin-container h1').text('Create Your Agent');
            $('#wpaa_create_agent_button').text('Create Agent');
            $('#delete_agent_link').hide();
            return;
        }

        //disable the agent from first
        $('#wpaa_create_agent_form').find('input, textarea, select').prop('disabled', true);
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
                    $('.wpaa-plugin-container h1').text('Update Your Agent');
                    $('#wpaa_create_agent_button').text('Update Agent');

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
                $('#wpaa_create_agent_form').find('input, textarea, select').prop('disabled', false);
            }
        });
    });


    // Agent List on Publish Page
    $(document).on('click', '.wpaa-agent-list2 li', function(e) {
        // If the click was on a child <a>, prevent default
        if ($(e.target).is('a')) {
            e.preventDefault();
        }
        var agent_id = $(this).find('a').data('agent_id');
        
        // Update selected state in the list
        $('.wpaa-agent-list2 li').removeClass('active');
        $(this).addClass('active');
        
        // Update the select and trigger change
        $('select[name="agent_id"]').val(agent_id).trigger('change');
    });


    // Publish Agent
    $('#wpaa-publish-agent-form').on('submit', function(e) {
        e.preventDefault();

        $('#wpaa_publish_agent_button').prop('disabled', true);
        $('#wpaa_publish_agent_button').text('Publishing...');

        var formData = new FormData(this);
        formData.append('action', 'wpaa_publish_agent');
        formData.append('nonce', wpaa_setting_nonce.nonce);

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
                $('#wpaa_publish_agent_button').prop('disabled', false);
                $('#wpaa_publish_agent_button').text('Publish Agent');
            },
            error: function(xhr) {
                showNotification('Error publishing agent. Please try again.', 'error');
                $('#wpaa_publish_agent_button').prop('disabled', false);
                $('#wpaa_publish_agent_button').text('Publish Agent');
            }
        });

    });
    

    
    // Add notification container if not present
    if ($('#wpaa-notification-container').length === 0) {
        $('body').append('<div id="wpaa-notification-container"></div>');
    }

    // Modern notification function
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="wpaa-notification wpaa-notification-${type}">
                ${message}
            </div>
        `);
        $('#wpaa-notification-container').append(notification);
        setTimeout(() => {
            notification.fadeOut(400, function() { $(this).remove(); });
        }, 2000);
    }

    // Delete Agent Confirmation Popup
    $(document).on('click', '.wpaa-delete-agent-link', function(e) {
        e.preventDefault();
        var agent_id = $(this).data('agent_id');
        
        // Remove any existing modal
        $('.wpaa-modal-overlay').remove();
        
        // Modal HTML
        var modalHtml = `
            <div class="wpaa-modal-overlay">
                <div class="wpaa-modal">
                    <button class="wpaa-modal-close" title="Close">&times;</button>
                    <div class="wpaa-modal-header">
                        <h2>Confirm Delete</h2>
                    </div>
                    <hr>
                    <div class="wpaa-modal-content">
                        <p>Are you sure you want to delete this agent?</p>
                        <div class="wpaa-modal-button">
                            <button id="wpaa-confirm-delete" data-agent_id="${agent_id}" style="background:#dc3545;">Delete</button>
                            <button id="wpaa-confirm-cancel" style="background:#aaa;">Cancel</button>
                        </div>
                    </div>
                    <hr>
                    <div class="wpaa-modal-footer">
                        <p>This action cannot be undone.</p>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modalHtml);
    });

    // Handle confirm delete
    $(document).on('click', '#wpaa-confirm-delete', function(e) {
        e.preventDefault();
        var agent_id = $(this).data('agent_id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpaa_delete_agent',
                nonce: wpaa_setting_nonce.nonce,
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
        $('.wpaa-modal-overlay').remove();
    });


    // Close modal on click
    $(document).on('click', '.wpaa-modal-close, .wpaa-modal-overlay, #wpaa-confirm-cancel', function(e) {
        if ($(e.target).hasClass('wpaa-modal-close') || $(e.target).hasClass('wpaa-modal-overlay') || $(e.target).is('#wpaa-confirm-cancel')) {
            $('.wpaa-modal-overlay').remove();
        }
    });


    // Save Access Key
    $('#wpaa_save_key_button').on('click', function(e) {
        e.preventDefault();
        $('#wpaa_save_key_button').prop('disabled', true);
        $('#wpaa_save_key_button').text('Saving...');

        var access_key = $('#access_key').val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpaa_save_key',
                nonce: wpaa_setting_nonce.nonce,
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
            $('#wpaa_save_key_button').prop('disabled', false);
            $('#wpaa_save_key_button').text('Save Access Key');
        }, 2000);
    });






    

});



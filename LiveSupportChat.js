class LiveSupportChat {

    // Executes when creating a new instance of the class
    constructor(options) {
        // Default options
        let defaults = {
            auto_login: true,
            php_directory_url: '',
            status: 'Idle',
            update_interval: 5000,
            current_chat_widget_tab: 1,
            conversation_id: null,
            notifications: true,
            files: {
                'authenticate': 'authenticate.php',
                'conversation': 'conversation.php',
                'conversations': 'conversations.php',
                'find_conversation': 'find_conversation.php',
                'post_message': 'post_message.php',
                'notifications': 'notifications.php',
                'logout': 'logout.php',
                'transfer': 'transfer.php'
            }
        };
        // Assign new options
        this.options = Object.assign(defaults, options);
        // Chat icon template
        document.body.insertAdjacentHTML('afterbegin', `
        <a href="#" class="open-chat-widget">
            <i class="fa-solid fa-comment-dots fa-lg"></i>
        </a>
        `);
        // Chat widget template
        document.body.insertAdjacentHTML('afterbegin', `
        <div class="chat-widget">
            <div class="chat-widget-header">
                <a href="#" class="previous-chat-tab-btn">&lsaquo;</a>
                <a href="#" class="close-chat-widget-btn">&times;</a>
            </div>
            <div class="chat-widget-content">
                <div class="chat-widget-tabs">
                    <div class="chat-widget-tab chat-widget-login-tab">
                        <form action="${this.options.files['authenticate']}" method="post">
                            <input type="text" name="name" placeholder="Your Name">
                            <input type="email" name="email" placeholder="Your Email" required>
                            <div class="msg"></div>
                            <button type="submit">Submit</button>
                        </form>
                    </div>
                    <div class="chat-widget-tab chat-widget-conversations-tab"></div>
                    <div class="chat-widget-tab chat-widget-conversation-tab"></div>
                </div>
            </div>
        </div>
        `);
        // Declare class variables for easy access
        this.openWidgetBtn = document.querySelector('.open-chat-widget');
        this.container = document.querySelector('.chat-widget');
        // Authenticate user if cookie secret exists
        if (this.autoLogin && document.cookie.match(/^(.*;)?\s*chat_secret\s*=\s*[^;]+(.*)?$/)) {
            // Execute GET AJAX request to retireve the conversations
            this.fetchConversations(data => {
                // If respone not equals error
                if (data != 'error') {
                    // User is authenticated! Update the status and conversations tab content
                    this.status = 'Idle';
                    this.container.querySelector('.chat-widget-conversations-tab').innerHTML = data;
                    // Execute the conversation handler function
                    this._eventHandlers();
                    // Transition to the conversations tab
                    this.selectChatWidgetTab(2);
                }
            });
        }
        // Execute event handlers
        this._eventHandlers();
        // Update chat every X
        setInterval(() => this.update(), this.options.update_interval);
    }

    // AJAX method that will authenticate user based on an HTML Form element
    authenticateUser(form, callback = () => {}) {   
        // Execute POST AJAX request and attempt to authenticate the user
        fetch(this.phpDirectoryUrl + this.files['authenticate'], {
            cache: 'no-store',
            method: 'POST',
            body: new FormData(form)
        }).then(response => response.text()).then(data => callback(data));      
    }

    // AJAX method that will Logout user
    logOutUser(callback = () => {}) {
        document.cookie = 'chat_secret=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        fetch(this.phpDirectoryUrl + this.files['logout'], { cache: 'no-store' }).then(response => response.text()).then(data => callback(data));
    }

    // AJAX method that will fetch the conversations list associated with the user
    fetchConversations(callback = () => {}) {
        fetch(this.phpDirectoryUrl + this.files['conversations'], { cache: 'no-store' }).then(response => response.text()).then(data => callback(data));       
    }

    // AJAX method that will fetch the conversation associated with the user and ID param
    fetchConversation(id, callback = () => {}) {
        fetch(this.phpDirectoryUrl + this.files['conversation'] + `${this.files['conversation'].includes('?')?'&':'?'}id=` + id, { cache: 'no-store' }).then(response => response.text()).then(data => callback(data));
    }

    // AJAX method that will transfer the conversation to another agent
    transferConversation(id, agentId, callback = () => {}) {
        fetch(this.phpDirectoryUrl + this.files['transfer'], {
            cache: 'no-store',
            method: 'POST',
            body: new FormData().append('id', id).append('agentId', agentId)
        }).then(response => response.text()).then(data => callback(data));
    }

    // Retrieve a conversation method
    getConversation(id, update = false, scrollPosition = null) {
        // Execute GET AJAX request
        this.fetchConversation(id, data => {
            // Update conversation ID variable
            this.conversationId = id;
            // Update the status
            this.status = 'Occupied';
            // Update the converstaion tab content
            if (!update) {
                this.container.querySelector('.chat-widget-conversation-tab').innerHTML = data;
            } else {
                let doc = (new DOMParser()).parseFromString(data, 'text/html');
                this.container.querySelector('.chat-widget-messages').innerHTML = doc.querySelector('.chat-widget-messages').innerHTML;
                this.container.querySelector('.chat-widget-message-header').innerHTML = doc.querySelector('.chat-widget-message-header').innerHTML;
            }
            // Transition to the conversation tab (tab 3)
            this.selectChatWidgetTab(3);  
            // Retrieve the input message form element 
            let chatWidgetInputMsg = this.container.querySelector('.chat-widget-input-message');
            // If the element exists
            if (chatWidgetInputMsg) {
                // Handle the content scroll position
                if (this.container.querySelector('.chat-widget-messages').lastElementChild) {
                    if (scrollPosition == null) {
                        // Scroll to the bottom of the messages container
                        this.container.querySelector('.chat-widget-messages').scrollTop = this.container.querySelector('.chat-widget-messages').lastElementChild.offsetTop;
                    } else {
                        // Scroll to the preserved position
                        this.container.querySelector('.chat-widget-messages').scrollTop = scrollPosition;
                    }
                }
                // Message submit event handler
                chatWidgetInputMsg.onsubmit = event => {
                    event.preventDefault();
                    // Retrieve the message input element
                    let chatMsgValue = chatWidgetInputMsg.querySelector('input[type="text"]').value;
                    if (chatMsgValue) {
                        // Decode emojis
                        chatWidgetInputMsg.querySelector('input[type="text"]').value = chatWidgetInputMsg.querySelector('input[type="text"]').value.replace(/([\u2700-\u27BF]|[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD10-\uDDFF])/g, match => '&#x' + match.codePointAt(0).toString(16).toUpperCase() + ';');
                        // Execute POST AJAX request that will send the captured message to the server and insert it into the database
                        fetch(chatWidgetInputMsg.action, { 
                            cache: 'no-store',
                            method: 'POST',
                            body: new FormData(chatWidgetInputMsg)
                        });
                        // Create the new message element
                        let chatWidgetMsg = document.createElement('div');
                        chatWidgetMsg.classList.add('chat-widget-message');
                        chatWidgetMsg.textContent = chatMsgValue;
                        chatWidgetMsg.innerHTML = chatWidgetMsg.innerHTML.replace(/\n\r?/g, '<br>');
                        // Add it to the messages container, right at the bottom
                        this.container.querySelector('.chat-widget-messages').insertAdjacentElement('beforeend', chatWidgetMsg);
                        // Reset the input form
                        chatWidgetInputMsg.querySelector('input[type="text"]').value = '';
                        chatWidgetInputMsg.querySelector('.files').value = '';
                        this.container.querySelector('.chat-widget-attachments').innerHTML = '';
                        // Scroll to the bottom of the messages container
                        this.container.querySelector('.chat-widget-messages').scrollTop = chatWidgetMsg.offsetTop;
                    }
                    // Focus the input message element
                    chatWidgetInputMsg.querySelector('input[type="text"]').focus();
                };
                // on change event handlers for attachments
                chatWidgetInputMsg.querySelector('.files').onchange = event => {
                    // Reset attachment label
                    document.querySelector('.chat-widget-attachments').innerHTML = '';
                    // Create attachment label
                    let attachmentLink = document.createElement('div');
                    attachmentLink.innerText = event.target.files.length + ' Attachment' + (event.target.files.length > 1 ? 's' : '');
                    document.querySelector('.chat-widget-attachments').appendChild(attachmentLink);
                    let removeAttachmentsLink = document.createElement('a');
                    removeAttachmentsLink.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                    document.querySelector('.chat-widget-attachments').appendChild(removeAttachmentsLink);
                    removeAttachmentsLink.onclick = event => {
                        event.preventDefault();
                        document.querySelector('.chat-widget-attachments').innerHTML = '';
                        chatWidgetInputMsg.querySelector('.files').value = '';
                    };
                };
                // Iterate all attachments in chat and add the event handler that will download them once clicked
                this.container.querySelectorAll('.chat-widget-message-attachments').forEach(element => element.onclick = () => {
                    element.nextElementSibling.querySelectorAll('a').forEach(element => element.click());
                });
                // Open attachment file dialog event handler
                if (chatWidgetInputMsg.querySelector('.actions .attach-files')) {
                    chatWidgetInputMsg.querySelector('.actions .attach-files').onclick = event => {
                        event.preventDefault();
                        chatWidgetInputMsg.querySelector('.files').click();
                    };
                }
                // Event handler that will open the emojis box when clicked
                chatWidgetInputMsg.querySelector('.actions .view-emojis i').onclick = event => {
                    event.preventDefault();
                    chatWidgetInputMsg.querySelector('.actions .emoji-list').classList.toggle('open');
                };
                // Iterate all emojis and add event handler that will add the particular emoji to the input message when clicked
                chatWidgetInputMsg.querySelectorAll('.actions .emoji-list span').forEach(element => element.onclick = () => {
                    chatWidgetInputMsg.querySelector('input[type="text"]').value += element.innerText;
                    chatWidgetInputMsg.querySelector('.actions .emoji-list').classList.remove('open');
                    chatWidgetInputMsg.querySelector('input[type="text"]').focus();
                });
            }
        });
    }

    // Update method that will update various aspects of the chat widget every X milliseconds
    update() {
        // If the current tab is 2
        if (this.currentChatWidgetTab == 2) {
            // Use AJAX to update the conversations list
            this.fetchConversations(data => {
                let doc = (new DOMParser()).parseFromString(data, 'text/html');
                this.container.querySelector('.chat-widget-conversations').innerHTML = doc.querySelector('.chat-widget-conversations').innerHTML;
                this._eventHandlers();
            }); 
        // If the current tab is 3 and the conversation ID variable is not NULL               
        } else if (this.currentChatWidgetTab == 3 && this.conversationId != null) {
            // Use AJAX to update the conversation  
            let scrollPosition = null;
            if (document.querySelector('.chat-widget-messages').lastElementChild && document.querySelector('.chat-widget-messages').scrollHeight - document.querySelector('.chat-widget-messages').scrollTop != document.querySelector('.chat-widget-messages').clientHeight) {
                scrollPosition = this.container.querySelector('.chat-widget-messages').scrollTop;
            } 
            this.getConversation(this.conversationId, true, scrollPosition);
        // If the current tab is 3 and the status is Waiting           
        } else if (this.currentChatWidgetTab == 3 && this.status == 'Waiting') {
            // Attempt to find a new conversation between the user and operator (or vice-versa)
            fetch(this.phpDirectoryUrl + this.files['find_conversation'], { cache: 'no-store' }).then(response => response.text()).then(data => {
                // If data includes automated message...
                if (data.includes('Msg: ')) {
                    // Check if message exists... We wouldn't want to add duplicates
                    let elementExists = false;
                    document.querySelectorAll('.chat-widget-message').forEach(element => {
                        if (element.innerHTML == data.replace('Msg: ','')) {
                            elementExists = true;
                        }
                    });
                    // If it doesn't exist, add it to the waiting chat
                    if (!elementExists) {
                        this.container.querySelector('.chat-widget-messages').innerHTML += `
                            <div class="chat-widget-message">${data.replace('Msg: ','')}</div>
                        `;
                    }
                } else if (data != 'error') {
                    // Success! Two users are now connected! Retrieve the new conversation
                    this.getConversation(data);
                }
            });               
        }
        // If notifications are enabled
        if (this.notifications) {
            // Fetch the notifications
            fetch(this.phpDirectoryUrl + this.files['notifications'], { cache: 'no-store' }).then(response => response.text()).then(data => {
                // Determine the current number of messages
                let numMessages = document.querySelector('.open-chat-widget').dataset.messages ? parseInt(document.querySelector('.open-chat-widget').dataset.messages) : 0;
                // If total number is greater than zero, update the open chat widget button data attribute
                if (parseInt(data) > 0) {
                    if (parseInt(data) > numMessages) {
                        new Audio('notification.ogg').play();
                    }
                    document.querySelector('.open-chat-widget').dataset.messages = data;
                } else if (document.querySelector('.open-chat-widget').dataset.messages) {
                    // If there are no new messages, delete the data attribute
                    delete document.querySelector('.open-chat-widget').dataset.messages;
                }
            });
        }
    }

    // Open chat widget method
    openChatWidget() {
        this.container.style.display = 'flex';
        // Animate the chat widget
        this.container.getBoundingClientRect();
        this.container.classList.add('open');
    }

    // Close chat widget method
    closeChatWidget() {
        this.container.classList.remove('open');
        // Animate the chat widget
        this.container.addEventListener('transitionend', () => {
            this.container.style.display = 'none';
        }, { once: true });
    }

    // Select chat widget tab method
    selectChatWidgetTab(tab) {
        // Hide all tabs
        this.container.querySelectorAll('.chat-widget-tab').forEach(element => element.style.display = 'none');
        // Display the selected tab
        this.container.querySelector(`.chat-widget-tab:nth-child(${tab})`).style.display = 'block';
        // Update the current chat widget tab variable
        this.currentChatWidgetTab = tab;
    }

    // Event handlers method
    _eventHandlers() {
        // Event handler for opening the chat widget
        this.openWidgetBtn.onclick = event => {
            event.preventDefault();
            this.openChatWidget();
        };
        // Event handler for closing the chat widget
        this.container.querySelector('.close-chat-widget-btn').onclick = event => {
            event.preventDefault();
            this.closeChatWidget();
        };
        // Event handler for switching between tabs
        this.container.querySelectorAll('.chat-widget-header a').forEach(element => {
            element.onclick = event => {
                event.preventDefault();
                if (element.classList.contains('previous-chat-tab-btn')) {
                    let prevTab = this.currentChatWidgetTab - 1;
                    if (prevTab < 1) {
                        prevTab = 3;
                    }
                    this.selectChatWidgetTab(prevTab);
                } else {
                    let nextTab = this.currentChatWidgetTab + 1;
                    if (nextTab > 3) {
                        nextTab = 1;
                    }
                    this.selectChatWidgetTab(nextTab);
                }
            };
        });
        // Event handler for authenticating the user
        this.container.querySelector('.chat-widget-login-tab form').onsubmit = event => {
            event.preventDefault();
            this.authenticateUser(event.target, data => {
                if (data == 'success') {
                    this.container.querySelector('.chat-widget-login-tab .msg').innerHTML = '';
                    this.container.querySelector('.chat-widget-login-tab form').reset();
                    this.fetchConversations(data => {
                        this.status = 'Idle';
                        this.container.querySelector('.chat-widget-conversations-tab').innerHTML = data;
                        this.selectChatWidgetTab(2);
                        this._eventHandlers();
                    });
                } else {
                    this.container.querySelector('.chat-widget-login-tab .msg').innerHTML = data;
                }
            });
        };
        // Event handler for logging out the user
        this.container.querySelector('.chat-widget-conversations-tab .logout-btn').onclick = event => {
            event.preventDefault();
            this.logOutUser(data => {
                if (data == 'success') {
                    this.status = 'Offline';
                    this.container.querySelector('.chat-widget-conversations-tab').innerHTML = '';
                    this.selectChatWidgetTab(1);
                }
            });
        };
        // Event handler for selecting a conversation
        this.container.querySelectorAll('.chat-widget-conversation').forEach(element => {
            element.onclick = event => {
                event.preventDefault();
                this.getConversation(element.dataset.id);
            };
        });
        // Event handler for transferring a conversation
        this.container.querySelectorAll('.chat-widget-transfer-conversation').forEach(element => {
            element.onclick = event => {
                event.preventDefault();
                let agentId = prompt('Enter the agent ID to transfer the conversation to:');
                if (agentId) {
                    this.transferConversation(element.dataset.id, agentId, data => {
                        if (data == 'success') {
                            this.container.querySelector('.chat-widget-conversation-tab').innerHTML = '';
                            this.selectChatWidgetTab(2);
                        }
                    });
                }
            };
        });
    }

    // Getter for the auto_login option
    get autoLogin() {
        return this.options.auto_login;
    }

    // Getter for the php_directory_url option
    get phpDirectoryUrl() {
        return this.options.php_directory_url;
    }

    // Getter for the status option
    get status() {
        return this.options.status;
    }

    // Setter for the status option
    set status(value) {
        this.options.status = value;
    }

    // Getter for the update_interval option
    get updateInterval() {
        return this.options.update_interval;
    }

    // Getter for the current_chat_widget_tab option
    get currentChatWidgetTab() {
        return this.options.current_chat_widget_tab;
    }

    // Setter for the current_chat_widget_tab option
    set currentChatWidgetTab(value) {
        this.options.current_chat_widget_tab = value;
    }

    // Getter for the conversation_id option
    get conversationId() {
        return this.options.conversation_id;
    }

    // Setter for the conversation_id option
    set conversationId(value) {
        this.options.conversation_id = value;
    }

    // Getter for the notifications option
    get notifications() {
        return this.options.notifications;
    }

    // Getter for the files object
    get files() {
        return this.options.files;
    }
}

// Usage example
let liveSupportChat = new LiveSupportChat({
    auto_login: true,
    php_directory_url: 'path/to/php/files/',
    status: 'Idle',
    update_interval: 5000,
    current_chat_widget_tab: 1,
    conversation_id: null,
    notifications: true,
    files: {
        'authenticate': 'authenticate.php',
        'conversation': 'conversation.php',
        'conversations': 'conversations.php',
        'find_conversation': 'find_conversation.php',
        'post_message': 'post_message.php',
        'notifications': 'notifications.php',
        'logout': 'logout.php',
        'transfer': 'transfer.php'
    }
});

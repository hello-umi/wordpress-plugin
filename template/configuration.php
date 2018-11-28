<div class="wrap">
    <form id="landbot-admin-form" class="postbox">
    <div class="form-group inside">
        <h1>Add Landbot</h1>
        <p>You can <a href="https://landbot.io" target="_blank">create an account here.</a></p>
                    
        <div class="content-section">
        <div>
            <label>1. Copy and paste your landbot token here*</label>
            <label>*You can find it under your Landbot > Share section</label>
        </div>
        <div class="authorization">
            <div> TOKEN: </div>
            <div>
            <input
                class="regular-text" 
                name="authorization"
                id="authorization"
                placeholder="Token 7beqb8161dcbw715018i1f26axa8901b94az563b"
                type="text"
            />
            </div>
        </div>
        </div>

        <div class="content-section">
        <div>
            <label>2. Display Format</label>
            <label>*The way Landbot is displayed</label>
        </div>
        <div>
            <div onclick="checkDisplayFormat('FULLPAGE')" class="square-display-format display-format-color">
            <div>
                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/img/full.png'; ?>" alt="fullpage"/>
            </div>
            <div> FULLPAGE </div>  
            </div>
            <div onclick="checkDisplayFormat('POPUP')" class="square-display-format display-format-color">
            <div>
                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/img/popup.png'; ?>" alt="popup"/>
            </div>
            <div> POPUP </div>
            </div>
            <div onclick="checkDisplayFormat('EMBED')" class="square-display-format display-format-color">
            <div>
                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/img/embed.png'; ?>" alt="embed"/>
            </div>
            <div> EMBED </div>
            </div>
            <div onclick="checkDisplayFormat('LIVE CHAT')" class="square-display-format display-format-color">
            <div>
                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/img/LIVECHAT.png'; ?>" alt="LIVE CHAT"/>
            </div>
            <div> LIVE CHAT </div>
            </div>
        </div>
        </div>
        
        <div class="content-section">
        <div>
            <label>3. More Options</label>
        </div>
        <div class="more-options">
            <div>
            <div class="option-check">
                <div>
                Hide background
                </div>
                <div class="check-button" >
                <div id="hideBackground" onclick="checkMoreOptions(this, 'hideBackground')" class="square-click left"></div>
                </div>
            </div>
            <div class="option-check">
                <div>
                Hide header
                </div>
                <div class="check-button" >
                <div id="hideHeader" onclick="checkMoreOptions(this, 'hideHeader')" class="square-click left"></div>
                </div>
            </div>
            </div>
            
            <div id="embed-selected"></div>
        
        </div>
        </div>
        
        <div id="alert-message"></div>
        
        </div>
        <div class="inside footer">
        <button class="button button-primary" id="landbot-admin-save" type="submit">
            Ok
        </button>
        </div>
    </form>
</div>
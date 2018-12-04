<div class="wrap">
    <form id="landbot-admin-form" class="postbox">
    <div class="form-group inside">
        <h1>Add Landbot</h1>
        <p>You can <a href="https://landbot.io" target="_blank">create an account here.</a></p>
                    
        <div class="content-section">
        <div>
            <label>1. Copy and paste your landbot URL here*</label>
            <label>*You can find it under your Landbot > Share section</label>
        </div>
        <div class="authorization">
            <div> URL: </div>
            <div>
            <input
                class="regular-text" 
                name="authorization"
                id="authorization"
                placeholder="https://landbot.io/u/H-107043-EUS4TOL95LM/index.html"
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
                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../img/full.png'; ?>" alt="fullpage"/>
            </div>
            <div> FULLPAGE </div>  
            </div>
            <div onclick="checkDisplayFormat('POPUP')" class="square-display-format display-format-color">
            <div>
                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../img/popup.png'; ?>" alt="popup"/>
            </div>
            <div> POPUP </div>
            </div>
            <div onclick="checkDisplayFormat('EMBED')" class="square-display-format display-format-color">
            <div>
                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../img/embed.png'; ?>" alt="embed"/>
            </div>
            <div> EMBED </div>
            </div>
            <div onclick="checkDisplayFormat('LIVE CHAT')" class="square-display-format display-format-color">
            <div>
                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../img/livechat.png'; ?>" alt="LIVE CHAT"/>
            </div>
            <div> LIVE CHAT </div>
            </div>
        </div>
        </div>
        
        <div class="content-section" style="float: left;">
        <div>
            <label>3. More Options</label>
        </div>
        <div class="more-options">
            <div>
            <div class="option-check">
                <div>
                Hide background
                </div>
                <div onclick="checkMoreOptions('hideBackground')" class="check-button" >
                <div id="hideBackground" class="square-click left"></div>
                </div>
            </div>
            <div class="option-check">
                <div>
                Hide header
                </div>
                <div onclick="checkMoreOptions('hideHeader')" class="check-button" >
                <div id="hideHeader" class="square-click left"></div>
                </div>
            </div>
            </div>
            
            <div id="embed-selected"></div>
        
        </div>
        </div>

        <div class="content-section" id="script-code" style="float: left;">
            <h3>If you prefer to embed your Landbot in the page code directly you have to copy this snippet and paste it where you want it to appear</h3>
            <hr/>
        </div>
        
        <div class="content-section" style="clear: both;">
          <div>
            <label>4. Select in which pages the Landbot will be visible</label>
          </div>  
          <ul id="list-pages">

          </ul>
        </div>
        
        <div id="alert-message"></div>
        
        </div>
        <div class="inside footer">
        <button class="button button-primary" type="submit">
           Save changes
        </button>
        </div>
    </form>
</div>
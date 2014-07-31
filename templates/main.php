<?php
\OCP\Util::addScript('contactstofb', 'script');
\OCP\Util::addStyle('contactstofb', 'style');

?>

<div id="app">
    <div id="app-navigation">
        <div>
            <h1>ToDos</h1>
            <ul>
                <li>Implement sync</li>
                <hr>
                <li>Userinterface for credentials and url</li>
                <li>Save credentials (encrypted) and url</li>
                <li>Display logfiles</li>
                <li>Start manually Sync!</li>
            </ul>
        </div>

        <div id="app-settings">
            <div id="app-settings-header">
                <button class="settings-button" tabindex="0"></button>
            </div>
            <div id="app-settings-content">

                <div>
                    <h2>Settings</h2>
                    <input type="text" name="url" placeholder="FRTZ!Box URL">
                    <br>
                    <input type="text" name="loginname" placeholder="Login name">
                    <br>
                    <input type="password" name="loginname" placeholder="Password">
                    <br>
                    <span>saved!</span>
                </div>

                <div>
                    <h2>Actions</h2>
                    <input type="button" value="Synchronize">
                </div>
            </div>
        </div>


    </div>
    <div id="app-content">

    </div>
</div>

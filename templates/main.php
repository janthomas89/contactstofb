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
                    <input type="text" name="url" placeholder="FRTZ!Box URL" value="<? p($_['settings']['url']); ?>">
                    <input type="text" name="loginname" placeholder="Login name" value="<? p($_['settings']['loginname']); ?>">
                    <input type="password" name="loginname" placeholder="Password" value="<? p($_['settings']['password']); ?>">
                </div>
                <div>
                    <input type="button" value="Synchronize now" id="app-sync-now" data-synchronizing-label="synchronizing ..." data-url="<? p($_['synchronizeUrl']); ?>">
                </div>
            </div>
        </div>


    </div>
    <div id="app-content">

    </div>
</div>

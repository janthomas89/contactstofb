<?php
\OCP\Util::addScript('contactstofb', 'script');
\OCP\Util::addStyle('contactstofb', 'style');

?>

<div id="app">
    <div id="app-navigation">
        <div class="container">
            <h2>Settings</h2>
            <form id="app-settings-form" method="post" action="<? p($_['settingsUrl']); ?>">
                <input type="text" name="url" placeholder="FRTZ!Box URL" value="<? p($_['settings']['url']); ?>">
                <input type="password" name="password" placeholder="Password" value="<? p($_['settings']['password']); ?>">
            </form>
        </div>

        <div class="container">
            <h2>Actions</h2>
            <input type="button" value="Synchronize now" id="app-sync-now" data-synchronizing-label="synchronizing ..." data-url="<? p($_['synchronizeUrl']); ?>">
        </div>

    </div>
    <div id="app-content">
        <div>
            <h1>ToDos</h1>
            <ul>
                <li>Implement sync</li>
                <hr>
                <li>Display logfiles</li>
                <li>Improce settings (HTTP(S), loginname, one number per entry, mobile label)</li>
                <li>write own fritz box API.</li>
                <li>Improve UI / ...</li>
            </ul>
        </div>

        <pre>

        <? foreach ($_['logEntries'] as $entry) { ?>

            <? var_dump($entry) ?>

        <? }  ?>

        </pre>

    </div>
</div>

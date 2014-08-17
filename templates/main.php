<?php
\OCP\Util::addScript('contactstofb', 'jquery.stickytableheaders.min');
\OCP\Util::addScript('contactstofb', 'script');
\OCP\Util::addStyle('contactstofb', 'style');

?>

<div id="app">
    <div id="app-navigation">
        <div class="container">
            <h2>Settings</h2>
            <form id="app-settings-form" method="post" action="<? p($_['settingsUrl']); ?>">
                <div>
                    <label for="app-settings-form-url">FRTZ!Box Settings</label>
                    <input type="text" name="url" id="app-settings-form-url" placeholder="FRTZ!Box URL" value="<? p($_['settings']['url']); ?>">
                    <input type="text" name="user" placeholder="User (optional)" value="<? p($_['settings']['user']); ?>">
                    <input type="password" name="password" placeholder="Password" value="<? p($_['settings']['password']); ?>">
                </div>

                <div>
                    <label for="app-settings-form-addressbook">Addressbook</label>
                    <select name="addressbook" id="app-settings-form-addressbook">
                        <? foreach ($_['addressBooks'] as $id => $name) { ?>
                            <option value="<? p($id) ?>"<? if($_['settings']['addressbook'] == $id) {p(' selected');} ?>>
                                <? p($name) ?> (<? p($id) ?>)
                            </option>
                        <? }  ?>
                    </select>
                </div>

                <div>
                    <input type="submit" value="Save" data-saving-label="saving ...">
                </div>
            </form>
        </div>

        <div class="container">
            <h2>Actions</h2>
            <input type="button" value="Synchronize now" id="app-sync-now" data-synchronizing-label="synchronizing ..." data-url="<? p($_['synchronizeUrl']); ?>">
        </div>

    </div>
    <div id="app-content">
        <!--
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
        -->

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Synced items</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="fileList">

                <? foreach ($_['logEntries'] as $entry) { ?>
                    <tr>
                        <td>
                            <? p($l->l('date', $entry->getDate()));?>
                            <? p($l->l('time', $entry->getDate()));?>
                        </td>
                        <td>
                            <? p($entry->getType()) ?>
                        </td>
                        <td>
                            <? p($entry->getSynceditems()) ?>
                        </td>
                        <td>
                            <? p($entry->getStatus()) ?>
                        </td>
                    </tr>
                <? }  ?>

            </tbody>
        </table>

    </div>
</div>

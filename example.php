<?php
/**
 * Instagram PHP implementation API
 * URLs: http://www.mauriciocuenca.com/
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Instagram PHP API example usage.
 * This script must be the one receiving the response from
 * instagram's servers after requesting an access token.
 * 
 * For example, if the redirect URI that you set up with instagram
 * is http://example.com/callback.php, this script must be named
 * callback.php and put at the root of your server so the access token
 * can be processed and all the actions executed.
 * 
 * http://example.com/callback.php must be replaced for REDIRECT-URI
 * in the following URI, along with your CLIENT-ID:
 * https://instagram.com/oauth/authorize/?client_id=CLIENT-ID&redirect_uri=REDIRECT-URI&response_type=token
 */
require_once 'Instagram.php';

/**
 * Configuration params, make sure to write exactly the ones
 * instagram provide you at http://instagr.am/developer/
 */
$config = array(
        'site_url' => 'https://api.instagram.com/oauth/access_token',
        'client_id' => '', // Your client id
        'client_secret' => '', // Your client secret
        'grant_type' => 'authorization_code',
        'redirect_uri' => '', // The redirect URI you provided when signed up for the service
     );

// Instantiate the API handler object
$instagram = new Instagram($config);
$popular = $instagram->getPopularMedia();

// After getting the response, let's iterate the payload
echo "<ul>\n";
$response = json_decode($popular, true);
foreach ($response['data'] as $data) {
    $link = $data['link'];
    $caption = $data['caption']['text'];
    $author = $data['caption']['from']['username'];
    $thumbnail = $data['images']['thumbnail']['url'];
?>
    <li><a href="<?= $link ?>"><img src="<?= $thumbnail ?>" title="<?= $caption ?>" width="150" height="150" border="0" align="absmiddle"></a> by <?= $author ?></li>
<?
}
echo "</ul>\n";

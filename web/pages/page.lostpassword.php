<?php
/*************************************************************************
This file is part of SourceBans++

Copyright � 2014-2016 SourceBans++ Dev Team <https://github.com/sbpp>

SourceBans++ is licensed under a
Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-nc-sa/3.0/>.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

This program is based off work covered by the following copyright(s):
SourceBans 1.4.11
Copyright � 2007-2014 SourceBans Team - Part of GameConnect
Licensed under CC BY-NC-SA 3.0
Page: <http://www.sourcebans.net/> - <http://www.gameconnect.net/>
*************************************************************************/

global $theme, $userbank;
if (isset($_GET['validation'], $_GET['email']) && !empty($_GET['email']) && !empty($_GET['validation'])) {
    $email      = $_GET['email'];
    $validation = $_GET['validation'];

    if (is_array($email) || is_array($validation)) {
        print "<script>ShowBox('Error', 'Invalid request.', 'red');</script>";
        Log::add("w", "Hacking attempt", "Attempted SQL-Injection.");
        PageDie();
    }

    if (strlen($validation) < 60) {
        echo '<div id="msg-red" style="">
			<i class="fas fa-times fa-2x"></i>
			<b>Error</b>
			<br />
			The validation string is too short.
			</div>';
        exit();
    }

    $q = $GLOBALS['db']->GetRow("SELECT aid, user FROM `" . DB_PREFIX . "_admins` WHERE `email` = ? && `validate` IS NOT NULL && `validate` = ?", array(
        $email,
        $validation
    ));
    if ($q) {
        $newpass = generate_salt(MIN_PASS_LENGTH + 8);
        $query   = $GLOBALS['db']->Execute("UPDATE `" . DB_PREFIX . "_admins` SET `password` = '" . $userbank->encrypt_password($newpass) . "', validate = NULL WHERE `aid` = ?", array(
            $q['aid']
        ));
        $message = "Hello " . $q['user'] . ",\n\n";
        $message .= "Your password reset was successful.\n";
        $message .= "Your password was changed to: " . $newpass . "\n\n";
        $message .= "Login to your SourceBans account and change your password in Your Account.\n";

        $headers = 'From: ' . SB_EMAIL . "\n" . 'X-Mailer: PHP/' . phpversion();
        $m       = mail($email, "SourceBans Password Reset", $message, $headers);

        echo '<div id="msg-blue" style="">
			<i><img src="./images/info.png" alt="Info" /></i>
			<b>Password Reset</b>
			<br />
			Your password has been reset and sent to your email.<br />Please check your spam folder too.<br />Please login using this password, <br />then use the change password link in Your Account.
			</div>';
    } else {
        echo '<div id="msg-red" style="">
			<i class="fas fa-times fa-2x"></i>
			<b>Error</b>
			<br />
			The validation string does not match the email for this reset request.
			</div>';
    }
} else {
    $theme->display('page_lostpassword.tpl');
}

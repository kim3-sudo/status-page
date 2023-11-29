<!doctype html>
<html>
  <head>
    <title>Install Status Page</title>
    <!-- Installer version 0.0.2 -->
  </head>
  <body>
    <h1>Install this status page software</h1>
    <h2>Prerequisites</h2>
    <p>YOU ARE FULLY RESPONSIBLE FOR CHECKING PREREQUISITES. The current version of the installation script does NOT check prerequisites for you!</p>
    <ul>
      <li>Operating System: CentOS (>7) OR RHEL (>7) OR Rocky Linux (>9.0)</li>
      <li>PHP (>8.0) AND php-mysqlnd (>8.0)</li>
      <li>MySQL (>8.0) OR MariaDB (>10.5)</li>
    </ul>
    <p>Windows, macOS, and other types of Linux (including Ubuntu Server) are not officially supported, but you may have success with these operating systems. Future official support for these operating systems may be added in the future.</p>
    <form action="run.php" method="post">
      <fieldset>
        <legend>Site Information</legend>
        <fieldset>
          <label for="organization">Organization</label>
          <input type="text" id="organization" name="organization" required>
          <p>This text is used for the copyright owner and some other filler texts.</p>
        </fieldset>
        <fieldset>
          <label for="organizationwebpage">Organization Webpage</label>
          <input type="text" id="organizationwebpage" name="organizationwebpage" required>
          <p>This address is linked to from the footer bar when your organization name is clicked.</p>
        </fieldset>
        <fieldset>
          <label for="feedbackwebpage">Feedback Webpage</label>
          <input type="text" id="feedbackwebpage" name="feedbackwebpage" required>
          <p>This address is linked to under Feedback in the footer. It should either be a web address or a <code>mailto:</code>.</p>
        </fieldset>
        <fieldset>
          <label for="privacypolicywebpage">Privacy Policy Webpage</label>
          <input type="text" id="privacypolicywebpage" name="privacypolicywebpage" required>
          <p>This address is linked to under Privacy Policy in the footer.</p>
        </fieldset>
        <fieldset>
          <label for="headerimageurl">Header Image URL</label>
          <input type="text" id="headerimageurl" name="headerimageurl" required>
          <p>This address should point to a logo or logotype for your organization. Valid extensions include <code>png</code>, <code>webp</code>, and <code>jpg</code>.</p>
        </fieldset>
        <fieldset>
          <label for="gethelpdestination">Get Help Destination</label>
          <input type="text" id="gethelpdestination" name="gethelpdestination" required>
          <p>This address is linked to the 'Get Help' button in the header.</p>
        </fieldset>
        <p>You can add additional site information in the System Settings page in the Admin Portal later, like your Google Analytics measurement ID and 'About This Page' descriptions.</p>
      </fieldset>
      <fieldset>
        <legend>Root User Information</legend>
        <fieldset>
          <label for="rootpass">Root User Password</label>
          <input type="password" id="rootpass" name="rootpass" required>
        </fieldset>
      </fieldset>
      <fieldset>
        <legend>Database Information</legend>
        <fieldset>
          <label for="dbhost">Database Host</label>
          <input type="text" id="dbhost" name="dbhost" required>
        </fieldset>
        <fieldset>
          <label for="dbschema">Database Schema</label>
          <input type="text" id="dbschema" name="dbschema" required>
        </fieldset>
        <fieldset>
          <label for="dbuser">Database Username</label>
          <input type="text" id="dbuser" name="dbuser" required>
        </fieldset>
        <fieldset>
          <label for="dbpass">Database Password</label>
          <input type="password" id="dbpass" name="dbpass" required>
        </fieldset>
        <p>Your database user should have <code>CREATE</code>, <code>DROP</code>, <code>INSERT</code>, <code>DELETE</code>, <code>SELECT</code>, and <code>UPDATE</code> privileges.</p>
      </fieldset>
      <fieldset>
        <legend>Continue Installation?</legend>
        <p>Continuing this installation may have unintended consequences! If you run this installer on a system which already has this software installed, it will ERASE all of your system settings and configurations. If you wish to continue, check the box below. You have been warned.</p>
        <input type="checkbox" id="installconfirm" name="installconfirm" value="true" required>
        <label for="installconfirm">Confirm?</label>
      </fieldset>
      <p>This installer will prepare software version 0.0.1b ("Grove Church") and database version 0.0.1 for use.</p>
      <button type="submit">Install Now</button>
    </form>
  </body>
</html>

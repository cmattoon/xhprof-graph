<?php /* This is the index file for the directory above the web root. */ ?>
<!DOCTYPE html>
<html>
    <head>
	<title>NeoXH</title>
	<style>html,body{margin:0;padding:1px;background: rgb(0, 117, 178);color: wheat;font-family: sans-serif;}h1{color:#fa4;width: 960px;margin: 0 auto 0.5em;border-bottom: 1px solid #999;padding-bottom: 0.25em;}.main{width: 960px;margin: 0 auto;background:#035580;text-align: center;overflow:auto}#webLink {background: rgb(255, 185, 57);color: rgb(0, 117, 178);padding: 1em;display: block;width: 10em;margin: 1em auto;text-decoration: none;border-radius: 0.2em;border: 1px solid #FFF;}pre.code{text-align: left;width: 400px;margin: 1em auto;border: 1px solid #222;padding: 0.25em 1em;background: #FFF;color: #333;}
	</style>
    </head>
    <body>
	<h1>NeoXH</h1>
	<div class="main">
	    <a id="webLink" href="web_gui/">NeoXH Web Interface</a>
	    <p>
		This is the index file for the directory above the web root.
		Click the link above to go to the web interface.
	    </p>
	    <pre class="code">## Example .htaccess access restriction
Order allow,deny
# To allow from a specific IP:
Allow from 192.168.1.2 
# To allow from a range of IP's 
Allow from 192.168.1.0\255.255.255.0
# Implicit deny
deny from all
	    </pre>
	</div>
    </body>
</html>
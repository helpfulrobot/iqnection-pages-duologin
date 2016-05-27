<h2>Authenticate with Duo</h2>
<% if $LogoutLink %><p><a href="$LogoutLink">Logout</a></p><% end_if %>
<iframe id="duo_iframe" data-host="$Host" data-sig-request="$sig_request" data-post-action="$PostLink"></iframe>


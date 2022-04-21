<h5 class="cmdirectoryentry__name">$Name</h5>

<p class="cmdirectoryentry__basic">
	<%t CS\Directory\Models\CMDirectoryBasicEntry.Phone %>: $Phone<br />
	<%t CS\Directory\Models\CMDirectoryBasicEntry.Email %>: $Email<br />
	<%t CS\Directory\Models\CMDirectoryBasicEntry.Website %>: <a href="$WebsiteLink" target="_blank">$Website</a>
</p>
<%if AddressLine1 %>
<address class="cmdirectoryentry__address">
	$AddressLine1<br />
	$AddressLine2<br />
	$Suburb<br />
	$City<br />
	$State<br />
	$Country
</address>
<% end_if %>

<%if MailingAddressLine1 %>
<address class="cmdirectoryentry__mailingaddress">
	$MailingAddressLine1<br />
	$MailingAddressLine2<br />
	$MailingSuburb<br />
	$MailingCity<br />
	$MailingState<br />
	$MailingCountry $MailingPostCode
</address>
<% end_if %>
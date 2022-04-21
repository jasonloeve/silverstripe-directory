<% include CMDirectoryBasicEntry %>

<div class="cmdirectoryentry__openinghours">
	<h6><%t CS\Directory\Models\CMDirectoryBusinessEntry.OpeningHours %></h6>

	<%-- Sunday --%>
	$TranslatedDay('sun'): <%if $Sunday %><%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenFrom %> $Sunday_From <%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenTo %> $Sunday_To
	<% else %>
	<%t CS\Directory\Models\CMDirectoryBusinessEntry.Closed %>
	<% end_if %>
	<br />

	<%-- Monday --%>
	$TranslatedDay('mon'): <%if $Monday %><%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenFrom %> $Monday_From <%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenTo %> $Monday_To
	<% else %>
	<%t CS\Directory\Models\CMDirectoryBusinessEntry.Closed %>
	<% end_if %>
	<br />

	<%-- Tuesday --%>
	$TranslatedDay('tue'): <%if $Tuesday %><%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenFrom %> $Tuesday_From <%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenTo %> $Tuesday_To
	<% else %>
	<%t CS\Directory\Models\CMDirectoryBusinessEntry.Closed %>
	<% end_if %>
	<br />

	<%-- Wednesday --%>
	$TranslatedDay('wed'): <%if $Wednesday %><%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenFrom %> $Wednesday_From <%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenTo %> $Wednesday_To
	<% else %>
	<%t CS\Directory\Models\CMDirectoryBusinessEntry.Closed %>
	<% end_if %>
	<br />

	<%-- Thursday --%>
	$TranslatedDay('thu'): <%if $Thursday %><%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenFrom %> $Thursday_From <%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenTo %> $Thursday_To
	<% else %>
	<%t CS\Directory\Models\CMDirectoryBusinessEntry.Closed %>
	<% end_if %>
	<br />

	<%-- Friday --%>
	$TranslatedDay('fri'): <%if $Friday %><%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenFrom %> $Friday_From <%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenTo %> $Friday_To
	<% else %>
	<%t CS\Directory\Models\CMDirectoryBusinessEntry.Closed %>
	<% end_if %>
	<br />

	<%-- Saturday --%>
	$TranslatedDay('sat'): <%if $Saturday %><%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenFrom %> $Saturday_From <%t CS\Directory\Models\CMDirectoryBusinessEntry.OpenTo %> $Saturday_To
	<% else %>
	<%t CS\Directory\Models\CMDirectoryBusinessEntry.Closed %>
	<% end_if %>
</div>

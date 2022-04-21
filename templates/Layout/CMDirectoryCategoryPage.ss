
<h1>{$Title}</h1>

{$Content}

{$DirectorySearchForm}

<% if $DirectoryAction == 'search' %>
	<div class="cmdirectory-results">
		<div class="cmdirectory-results__count"><%t CS\Directory\CMDirectoryController.SearchResultsFound num=SearchResults.count %></div>
		<% if SearchResults.count %>
		<div class="cmdirectory-results__items">
		<% loop $SearchResults %>
			<div class="cmdirectoryentry $ClassName.lowercase">
				$Me
			</div>
		<% end_loop %>
		</div>
	<% end_if %>
	</div>
<% end_if %>

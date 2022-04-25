<% include BreadCrumbs %>
<div class="container">
	<div class="row">
	<% if $Level(2) %>
		<div class="sidebar">
			<% include Sidebar %>
		</div>
		<div class="has-sidebar">
	<% else %>
		<div class="no-sidebar">
	<% end_if %>


		<h1>{$Title}</h1>

		<div class="content">{$Content}</div>

		<div class="b-form">{$DirectorySearchForm}</div>

		<% if $DirectoryAction == 'search' %>
			<div class="cmdirectory-results">
				<div class="cmdirectory-results__count"><%t CMDirectoryController.SearchResultsFound num=SearchResults.count %></div>
				<% if SearchResults.count %>
				<div class="cmdirectory-results__items">
					<div class="row small-gutters">
						<% loop $SearchResults %>
							<div class="col-md-6 cmdirectoryentry $ClassName.lowercase">
								<div class="inner" data-mh="cmd">
								$Me
								</div>
							</div>
						<% end_loop %>
					</div>
				</div>
			<% end_if %>
			</div>
		<% end_if %>


	</div> <!-- .has-sidebar/.no-sider -->
	</div> <!-- .row -->

	<% include PageUtilities %>
	<% include MobileSidebar %>
</div><!-- .container -->

test

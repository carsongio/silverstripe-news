<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
<article>
    <h1>$Title</h1>
    <div class="content">$Content</div>
</article>


<span>Nesw list</span>
<br/>
<br/>
<br/>
<% loop PaginatedNews %>
    <p>$Date.Nice</p>
    <% loop Translation %>
        <a href="$Link">$Title</a>
    <% end_loop %>
    <br/>
<% end_loop %>

<% if PaginatedNews.MoreThanOnePage %>
 		<% if PaginatedNews.NotFirstPage %>
 			<a class="prev" href="$PaginatedNews.PrevLink">Prev</a>
 		<% end_if %>
 		<% loop PaginatedNews.Pages %>
 			<% if CurrentBool %>
 				$PageNum
 			<% else %>
 				<% if Link %>
                    <a href="$Link">$PageNum</a>
            	<% else %>

                <% end_if %>
            <% end_if %>
        <% end_loop %>
    <% if PaginatedNews.NotLastPage %>
        <a class="next" href="$PaginatedNews.NextLink">Next</a>
    <% end_if %>
<% end_if %>

$Form
$PageComments
</div>
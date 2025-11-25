# Routes: Public Frontend

| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | / | home | HomeController@index |
| GET | /blog | posts.index | PostController@index |
| GET | /blog/{slug} | posts.show | PostController@show |
| GET | /category/{slug} | categories.show | CategoryController@show |
| GET | /tag/{slug} | tags.show | TagController@show |
| GET | /author/{username} | authors.show | AuthorController@show |
| GET | /archive/{year}/{month?} | archives.show | ArchiveController@show |
| GET | /search | search.results | SearchController@results |
| GET | /{slug} | pages.show | PageController@show (catch-all) |

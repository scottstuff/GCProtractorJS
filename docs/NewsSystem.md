## News System Requirements

### Front-End

* NewsController
  * archiveAction - /news - News/archive.html.twig
  * articleAction - /news/{id}/{slug} - News/article.html.twig
  
### Back-End

* Admin/NewsController
  * archiveAction - /admin/news - Admin/News/archive.html.twig
  * createAction - /admin/news/create - Admin/News/create.html.twig
  * editAction - /admin/news/edit/{id} - Admin/News/edit.html.twig
  
### Models

* NewsArticle
  * id (int, AUTO_INCREMENT)
  * title (string, 255, NOT NULL)
  * author (FK -> User)
  * category (FK -> NewsCategories)
  * dateCreated (DateTime)
  * lastModified (DateTime)
  * publishDate (DateTime) - _Future publish dates will hide the post until that date._
  * published (Boolean) - _Draft posts are isPublished == false._
* NewsArticleContent - _This table will hold the full post content for each article._
  * article_id (FK -> NewsArticle, UNIQUE)
  * content (TEXT)
* NewsCategory - _This will control the tabs at the top of the page. (Latest News, Winners)_
  * id (int, AUTO_INCREMENT)
  * name (string, 255)
  * shortName (string, 60) - _Internal name used in code (id for tabs, etc)_
* NewsTag - _May not be implemented, initially._
  * id (int, AUTO_INCREMENT)
  * name (string, 255)
* NewsArticleTags - _Join table for M2M Article <-> Tags Relationship; May not be implemented, initially._
  * article_id (FK -> NewsArticle)
  * tag_id (FK -> NewsTag)

### Layout Updates

Oz requested that we add some kind of archive listing into the right column of
the News pages. Either a full list of articles with dates or maybe we can add
a tree view of Year -> Month -> Post (Date).

He also requested pagination of articles in the archive.

### Work Outstanding

- [ ] Create Entities
- [ ] Generate Migrations
- [ ] Create Fixtures for existing News Articles
- [ ] Add Admin Archive List Page
- [ ] Add Admin Create Article Page
- [ ] Add Admin Edit Article Page
- [ ] Add Public Archive List Page
- [ ] Add Public Individual Article Page
- [x] Add Tabs to Public Archive for "Latest News" and "Winners"
- [x] Add Right Column Archive View to News Pages
- [ ] Add Pagination

### Where I Left Off

- Layout work for the tabs (each category is a tab, name = Title, shortName = internal reference)
- Layout work for the right column "Latest Articles" (5 most recent articles)

### Was about to start

- Generating migrations to test database structure.
- Loading existing articles into fixtures.

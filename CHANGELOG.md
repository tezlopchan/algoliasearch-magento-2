# CHANGE LOG

## 3.10.1

### UPDATES
- Added recommended js version in readme file.

### Bug Fixes
- Add caching on category name lookup (scoped by store) to fix slowness in indexing.
- Prevent loss of synonyms while copying from tmp index during Indexing in Algolia Dashboard.
- Fixed the translation issue of labels in Algolia autocomplete dropdown
- Ensured compatibility of the extension with PHP 7.4
- Resolved the deployment issue related to prefixing Magento database tables


## 3.10.0

### UPDATES
- Updated the sorting strategy to select between using Virtual Replica v/s Standard Replica. 
- Deprecated the set up of Algolia synonyms from Magento admin
- Updated Algolia Recommend version to 1.8.0 and refactored the corresponding code.
- Added code to delete category from Algolia when the category is deleted from Magento.
- Added keyboard navigation in autocomplete
- Added search input placeholder translation in the autocomplete.
- Added 10 SKU limit in SKU indexing form in the Magento admin.
- Added configuration to enable/disable the searchBox widget on the instant search page.


### FIXES
- Fixed the issue with the price filter in Algolia Merchandiser.
- Fixed the product price issue when the special price and catalog price rules are present.
- Fixed the group price issues for all product types [Simple, Configurable, Bundle, Group, Downloadable, and Virtual Product].
- Fixed the Algolia objectID issue in Recommend
- Fixed the issue on the instant search page where the current search term was not reflecting on the page title.
- Fixed the TagName error in autocomplete.
- Fixed the issue with stock indexing when manage stock is set to No
- Added code to prevent full indexing after placing order when MSI is disabled


## 3.9.1

### UPDATES
- Refactored the Autocomplete to provide an extensible model for function-based templates by utilizing tagged template literals. The approach supports the use of RequireJS mixins for overriding the template functionality. 
- Update the synonym area notice in the Magento admin to point customers to use the Algolia dashboard for Synonym management as Magento Dashboard will be deprecated in a future release
- Refactored the casting Attributes
- Managing Max record size via the admin


### FIXES
- Autocomplete category links not preselecting facets on the target URL
- Fixed bug related to conjunctive facets and adaptive images in autocomplete
- Fixed issue with showing __empty__ in the url if autocomplete was disabled 
- Fixed autocomplete suggestions
- Instant search fixes when the price was set to retrievable = 'no'
- Price attribute fixes in autocomplete when the price attribute is set to Non-Retrievable
- Add to cart triggering duplicate view event for the Algolia recommend products
- Issues while saving and loading data by the wrong cache key for the popular queries
- Issues with max_retries in clear old jobs function in the queue
- Place Order duplicate Conversion issue for Grouped Product
- Fixes issues with store-specific category index



## 3.9.0

### New Features
- Trends Recommendations: We have added the ability to add Trending Items to the PDP and the shopping cart page. More information can be found <a href="https://www.algolia.com/doc/integration/magento-2/how-it-works/recommend/?client=php#trending-items">here</a>. We also provide a <a href="https://www.algolia.com/doc/integration/magento-2/how-it-works/recommend/?client=php#configure-the-trending-items-widget">Trending Items widget</a> that can be used to add Trending Items to any page.
- Added an option to show Recommend Related and Frequently Bought Together products on the shopping cart page.
- Added an option to enable the Add To Cart button for all types of recommended products (Related, Frequently Bought Together, and Trending Items).
- Added Algolia Recommend dashboard link on the Magento dashboard
- Added Algolia Search extensions release notes link in the Magento admin to be able to access release notes easily.
- Implemented Recommended Product click event using personalization.

### UPDATES
- Refactored the Algolia Insight functionality in the extension code base per Magento standard (moved the observer directory in the module root).
- Refactored the autocomplete 2.0 code to make it more developer-friendly to allow for customization per customer needs.
- Collated all autocomplete-specific logic in a single autocomplete.js file and segregated JS-based templates that control the layout of the different autocomplete sources to be more developer-friendly. This enables the customers to easily override the layout of the autocomplete menu in the custom theme and the extension.


### FIXES
- Click event in autocomplete
- Autocomplete errors if the product is not assigned a category and indexed into Algolia
- Issues with the price attribute in autocomplete when price attribute is set to Non-Retrievable
- The autocomplete in Query merchandiser (in the Magento admin) shows products from the default store on switching stores [Fixed]
- Issues with triggering Add to Cart Conversion for Configurable Product
- Issues with indexer not updating when product goes out of stock when the last of the inventory is done


## 3.8.1

### UPDATES
- Updated the system configuration message for Click & Conversion Analytics 
- Added validation in synonyms upload section (for Algolia Search) in the Magento admin(#1226)
- Updated code to set "Filter Only" facets via the instantsearch/facets settings in the Magento admin panel(#1224)
- Updated CSR policy to fix content security error for insights.io(#1228)

### FIXES
- Fixed the InstantSearch variant image issue(#1223)
- Fixed the proper case for 'Related Products' in comment Magento Admin (#1221)
- Fixed the code deploy error if the DB has tables with Prefix - patch not applied(#1229)
- Fixed the Remove trailing ? url in category page(#1222)

## 3.8.0

### New Features
- Added Algolia Recommend(#1212)

### UPDATES
- Updated Autocomplete V1.6.3 (#1217)

### FIXES
- Fixed the analytics dashboard issue in the magento admin(#1215)
- Fixed the compatibility issue with landing page (#1216)
- Fixed the issue with in_numeric in search suggestion indexing(#1218)

## 3.7.0

### UPDATES
- Updated instantsearch.js(#1203)

### FIXES
- Fixed the autocomplete.js vulnerability issues (#1205)
- Fixed the issue with PHP 8.1: Deprecated Functionality: explode() on category page (#1208)
- Fixed the issue with Mview update by patch (#1210)

## 3.6.1

### UPDATES
- Updated Algolia Php Client version 2.5.1 to 3.2.0(#1200)

## 3.6.0

### UPDATES
- Implemented DB schema using the Declarative schema(#1196)
- Updated to support the latest released Magento 2.4.4 with PHP 8.1(#1196)
- Updated to support Guzzle Http version with 6.3 and 7.4 for compatibility with Magento 2.4.x  (#1187)
- Updated to support the latest released Magento 2.4.4 with PHP 7.4 (#1173)
- Updated PHP Unit Integration Test for magento 2.4.4 (#1176)
- Release Notes Automation using github release.yml (#1174)

### FIXES
- Fixed the merchandised query on Query Merchandiser to display query-based products for merchandising (#1177)
- Fixed the issue with userToken not being sent to Algolia for the logged-in user for personalisation (#1178)
- Fixed the issue with requirejs not being defined on the swagger (#1172) 

### TOOLING
-  Update CS fixer with the Version 3 (#1173)

## 3.2.0

### UPDATES
- Update module sequence and indexers (#1132)
- Add additional attributes for customer groups (#1144)
- Add priceRanges back to uistate (#1151)
- Set visibility of getTaxPrice() to public in ProductWithoutChildren (#1159) (@JeroenVanLeusden)
- Improve check: and try to handle URL by URL rewrites if request path is NULL. (#1149) (@vmalyk)
- Unfilter getCoreCategories() from private getCategoryById() (#1154)
- Add ability to change region for Analytics call (#1131) (@bchatard)

### FIXES
- Add missing indexName for autocomplete sections (#1133)
- Add check if color object is defined for adaptive images (#1135)
- Add small change on category indexing for Enterprise Edition (#1136)
- Refactor loops and fix analytics overview issues (#1137)
- Fix missing 'out of' translation on search results page (#1139) (@NickdeK)
- Restore query value in custom instant search box (#1142) (@vmalyk)
- Fix instantsearch rangeInput labels (#1148)
- Recalculate special price if zero default pricing (#1160)
- Add missing label for queue method rebuildCategoryIndex (#1152) (@tezlopchan)
- Fix max original price for product with children (#1155) (@valeriish)
- Store scope category merchandising based on store_id param (#1156)
- Fix Subproduct Image Data (#1157)
- Add try/catch to add to cart push events (#1158)
- Fix for BE side renderting and caching 2 types of content (#1161) (@sudma)
- Fix: categories not included filtering on product listing (#1163)
- Fix: removing old feature checking for C&C that triggers deprecated endpoint (#1164)

### TOOLING
-  Update CI testing (#1140)

## 3.1.0

### UPDATES
- Fetch Algolia additional data only in the extension's sections in the Magento config (#1119)
- Remove image URL manipulation (#1120) by @fredden
- Add product source hook to modify search options (#1123) 
- Use button element for search button (#1102) by @fredden
- Set Price Calculation to true for every tax field during price calculation (#1124)
- Update maintainers.md with process info (#1128) 
- Update algoliaBundle with latest instantsearch version (#1127) 

### FIXES
- Fix to keep the price slider values in the filter when user refresh the page (#1121)
- Add missing handle for cms editor layout needed for pagebuilder (#1122) 
- Adding credentials check to prevent Magento from crashing on fresh install (#1125)

## 3.0.2

### FEATURES
- CMS Page indexing improvement (#1113) (by @vmalyk)

### UPDATES
- Add tags for QM and LPB queries to use VM if applicable (#1103)
- InfiniteHits change showMoreLabel to showMoreText (#1105)
- Turn off Perso when it’s not available in the plan but activated in the Magento admin (#1104)
- Keep custom Rules set directly on Algolia dashboard for CMS Pages, Suggestions and Additional sections. (#1106)
- Remove from composer suggestion for es compatibility (#1114)

### FIXES
- linter fix templates (#1108)
- Configurable product with broken image when set to “no_selection” (#1101) (by @dverkade)
- Insights Analytics - Fix Add to Cart Conversion (#1111)
- Added try/catch on CheckoutSuccess event (#1112)
- Merge 2 connect-src policy sections to 1 (#1110) (by @vmalyk)

## 3.0.1

### UPDATES
- Added some configuration to algoliaConfig JS object (#1097) 
- Update csp for proxy and update admin bundle (#1098) 

### FIXES
- Switch from mt_rand to random_int to meet marketplace expectations (#1095)
- Indexing queue grids fixes (#1096) 

## 3.0.0
This update will **break compatibility** if you're using the backend facets feature. Please read the [Magento 2.4 section](https://www.algolia.com/doc/integration/magento-2/getting-started/quick-start/#magento-24-compatibility) of our documentation to get more information about it. 

## New Features
- Compatibility with Magento 2.4

### UPDATES
- Remove less than IE9 condition from configuration template (#1068) 
- Add messaging for indexing queue and logs (#1070) 
- Remove IdentityInterface from job model class (#1071) 
- Convert condition to conditions formatting (#1072)
- Remove backend facets and Mysql Adapter (2.4 compatibility) (#1073) 
- Update PHP and magento framework versions (#1074) 
- Backport: Added algolia/algoliasearch-inventory-magento-2 to suggest (#1075) @vmalyk
- Remove PHP requirements from Composer (#1077) @vmalyk
- Set ACL resource titles are translatable. (#1080) @vmalyk
- If order not found fetch order from first order ID (#1081) 
- Exclude category facets from clearRefinement on category page (#1083)
- Update bundle with updated IS and autocomplete versions (#1084) 
- Add Customisation section to README.md (#1086) 

### FIXES
- Fix microdata on instantsearch (#1065) @flagbit
- Instantsearch Category Filter when category facet is not configured (#1069)
- Fix serialization issue with 2.4 (#1079) 
- Add image check to skip if placeholder for adaptive imgs (#1082)

### TOOLING
- Update CircleCi for Magento v2.3.5-2 and v2.4.0 (#1078)

## 2.0.2

### UPDATES
- Update setUserToken to cap character length (#1058) 
- Set forwardToReplicas for copy rules to false (#1059) 

- Use current store id to get settings for replicas (#1057) @flagbit
- Make sure original price range is saved for configurables (#1015) @flagbit

### FIXES
- Restore "search as you type" feature (#1061) 
- Fix error on URL during Pages indexing (#1012) @flagbit

## 2.0.1

### UPDATES
- Update the copyQueryRules method to use api client copyRules (#1029) 
- Removed obsolete trigger for catalog_product_entity_media_gallery from mview.xml (#1027) @vmalyk
- Add csp_whitelist for services (#1039) 
- Refactor getSalesData() method for optimisation (#1034) 
- Activate "filterPromotes" attribute for created Merchandising Rules (#1043)
- Add category_without_path only if categories is searchable (#969) @VincentMarmiesse

### FIXES
- Prevent division by zero in the Notice Helper when configuration is not set (#1026)
- Remove extra css import (#1013) @flagbit
- Bundle products collection to return getItems array (#1038) 
- Switch from priceRanges to input ranges widget for ISV4 (#1042) 
- Check the right storeId for moveIndex (#1016) @flagbit


### TOOLING
- Remove composer self-update from circleCI quality tools (#1032) 
- Update CircleCI quality tools to remove composer set and install (#1033) 

## 2.0.0
With the release of a new major version, we have decided to create minor and major version releases to allow those that want to continue on the minor version. This update will **break compatibility**. Please read the [upgrade guide](https://www.algolia.com/doc/integration/magento-2/getting-started/upgrading/#upgrading-from-v1-to-v2) for all of the file changes and updates included in this release. 

If you would like to stay on the minor version, please upgrade your composer to only accept versions less than version 2 like the example:

`"algolia/algoliasearch-magento-2": ">=1.13.1 <2.0"`

## New Features
- Algolia PHP Client v2 (from v1) (#848, #968, #984, #990) @DevinCodes  
- [Instantsearch v4](https://www.algolia.com/doc/guides/building-search-ui/upgrade-guides/js/) (from v2) (#838, #912) @tkrugg
- [Personalization](https://www.algolia.com/doc/integration/magento-2/how-it-works/personalization/) (#994, #998) 

## 1.13.1

### UPDATES
- Add warning after image cache is flushed (#983)

### FIXES
- Fix bundle product selections for subproducts (#982)
- Fixes visibility in B2B / Catalog Permissions raw SQL (#977)

## 1.13.0

## FEATURES
- Indexing Queue Log admin view (#929)

### UPDATES
- Add documentations links on top of the Indexing Queue page (#931)
- Archive logs cleaning (#928) 
- Admin notices refactoring (#921)
- Join 2 callbacks for autocomplete updated event (#926) @vmalyk
- Replace class names to ::class instead string names (#936) @vmalyk
- Added translation to ui components and source options (#938) @vmalyk
- Adminhtml improvements: XSS prevention, translations, etc (#939) @vmalyk
- Tests: improve class names usage and replace literal name to ::class (#940) @vmalyk
- Clean up adminhtml queue controllers (#968)

### FIXES
- Prevent type error for backend facet query (#911)
- B2B countable error in ProductDataArray class (#915)
- Hide out of stock for configurable products (#925) @vmalyk
- Remove "Replace categories" config dependency (#930)
- PHP 7.2 warning error fixes (#932)
- Remove Object manager and add factories to constructor (#937) @vmalyk
- Fix Landing Page Builder remove url rewrite when disabled (#944)
- Re-add forgotten indexOutOfStockOptions() method behaviour (#954)
- Use Guzzle to for version checking (#949)
- Fix configurable image condition (#964)

## 1.12.1

This release has been made possible thanks to the involvement of the community, with about half of the pull requests merged coming from the Magento ecosystem.

The Magento team at Algolia really wanted to thank our amazing community for its help.
For this release, a big shout out for:

- @DavidLambauer
- @JosephMaxwell
- @peterjaap
- @unicoder88
- @VincentMarmiesse
- @vmalyk

Thanks A LOT for your PRs, we really appreciate!

What this release brings:

### FEATURES
- Add extension notifier (#868)

### UPDATES
- Removed objectManager from ProductHelper (#814) (by @peterjaap)
- Removing the BaseAdminTemplate block (#822)
- Add config warnings for ES and MSI (#889)
- Remove catalog index price update by schedule subscription (#870) (by @unicoder88)
- Added alt attribute for img tags (#896) (by @vmalyk)
- Add "suggest" section modules in composer.json (#898) (by @vmalyk)
- Create replicas if Backend Facet Rendering is enabled (#902) (by @VincentMarmiesse)

### FIXES
- Fixed the dependency list (#843) (by @DavidLambauer)
- Pass set product website_id to load catalogrule prices with enabled customer groups (#853) (by @unicoder88)
- Clean scope code resolver when starting environment emulation (#857) (by @unicoder88)
- Fixing order of setting parameters (#859) (by @JosephMaxwell)
- Fixed Composer requirements and README.md (#884) (by @vmalyk)
- Fix Tier Price calculation (#887)
- Fix autocomplete additional sections link URL (#891)
- Fix errors in code instead ignore in PHPStan (#878) (by @vmalyk)
- Fix product image helper method to return set images (#899)
- Fix version to 1.12.1 for unsubscribe Mview migration (PR #870) (#910) (by @vmalyk)

### TOOLING
- Events tracking (#805)
- chore: fixes styleci configuration (#847)
- chore(ignores-phpstan-fixed-errors): sets reportUnmatchedIgnoredError (#877)
- ci: quality assurance tools  (#882)
- Ci/quality tools (#886)
- chore: increases phpstan level to 1  (#892)
- Make changes to pass new Marketplace test expectations (#906)

## 1.12.0

### FEATURES
-  Algolia's facets backend rendering for Mysql Engine (#802) 

### UPDATES
- The extension no longer supports Magento 2.1 **BC Break**
- B2B Feature : add condition for allow catalog browsing for enabling and add count check for list() (#820)
- Updated enabled logic to pull from the config helper class  (#820)
- Add ACL for Algolia Search configuration section (#829)
- Remove the isQueueActive() check for the product plugin (#830) 
- Update get product images (#823) 
- Remove Circle CI 2.1 check (#849) 
- Add facet query rules management + dashboard warnings (#844)
- Update video links in admin (#850) 
- Add new support page with tabs (#845) 
- Adjust the extension to be ready for upcoming MSI optional support that will come through another extension. (#841)
- Making PHPCompatibility assess no funky <7.1 PHP is used in our project
- Magento Cloud Development setup teardown (#860) 
- Add type checking tool configuration (#861) 
- Add notice when users have access to C&C Analytics but they haven't turned it on (#867)

### FIXES
- Add Store emulation for full category reindexing (#826) 
- Category Product Updates for Update on Schedule (#819) 
- Fix disabled autocomplete with active facet query rule) (#866)

## 1.11.3

### UPDATES
- Add compatibility with Magento 2.3.2 Search Adapter (#806)
- Batch size proccess of affected products after category update (#811) 

## 1.11.2

### UPDATES
- Fix setSettings on TMP index when settings were not properly merged (#785)
    * As well the settings are now set to the index immediately before the `move` operation so no settings are lost
- Update Plugins observers for Magento v2.1 compatibility (#783)
    * Use `after` and `before` plugin methods instead of `around` methods which increases DX and performance of the extension
- Update to Category Merchandising by adding notification for category display mode, 50 pins limit, and spacing update to Merchandising page (#795)
- Add price ranges widget to Landing Page Builder product grid (#779)
- Add storeID to getFacets for multi-store compatibility (#789)

## 1.11.1

- Fixed compatibility with PHP 5.6 (#776, #780) 
- Category plugin now uses "before" and "after" merhods instead of "around" methods (#775)
- Fetch "image" attribute for Product object during indexing (#772)

## 1.11.0

### FEATURES
- Tutorial videos inside the admin configuration (#704)
  * Added Youtube tutorial videos on top of each relevant configuration page of the extension.
- B2B Catalog Permissions (#695)
  * Added support for Magento Commerce (EE) edition features: Catalog Permissions and B2B Shared Catalog.
- Query merchandiser (#739) 
  * Feature to let you promote or demote products for a search. Based on Algolia's Query rules.

### UPDATES
- Refactor categories to be indexed by batches (#696)
- Translate HTML text in hit template (#702) 
- Refactored Queue mechanism (#698 #713 #740)
- Add native price facet handling (#700) 
- Branding Logo Upsell (#705)
- Better UX and copy on Analytics overview page (#712) 
- Use factory to create new AlgoliaSearch\Client (#722)
- Add an export button to the indexing queue grid for troubleshooting (#717)
- Update indexing queue grid collection to add status column for sortability (#718)
- Add "algoliaBundle" as parameter for instant search hooks (#725) 
- Encode search query sent to the backend from JS (#721) 
- "algolia_pages indexer" emulation restructure and added try/catch for debugging (#728)
- Show indexer warning when type mismatch between Algolia Products and Price Indexer (#734)
- Add afterAutocompleteStart hook (#747) 
- Add Max Record Size Limit (#746) 
- Change "let" for "var" in javascript files (#754)

### FIXES
- Keep ruleContexts when algoliaConfig.areCategoriesInFacets is false (#693)
- Fix Analytic Overview Update Action (#703)
- Prevents replicas to be created if InstantSearch Result Page is disabled (#694)
- Fix customer group prices on grouped products (#726) 
- Pull URLs of correct image types (#727)
- Fix SVG icons (#738) 
- Fix suggestions functionality when "All Departments" string is translated (#744)
- Fix Searchable attributes / Unretrieveable attributes configuration not saving correct scope (#750)

## 1.10.0

### FEATURES

- Landing Page Builder (#661, #687)
  * Feature to let you build dynamic merchandised landing pages based on Algolia results
- Products are reindex when catalog rule is applied (#666)
  * The extension is now subscribed to to changes in price index
- Extension's documentation was moved to [official Algolia documentation](https://www.algolia.com/doc/integration/magento-2/getting-started/quick-start/)
  * Links inside the extension was changed (#686)

### UPDATES

- Updated Reindex SKU form (#657)
  * Improved try/catches to allow some store views to continue when one of them doesn't qualify
  * Updated store name to show from which website/store group/store view
  * Updated global attributes to not include store since it is not necessary
- Updated minimal version of PHP API client in Composer.json to meet extension's dependencies (#671)
- Optimized fetch of child products during reindex of a configurable product (#664)
  * Improves performance - the indexing time of a configurable product dropped by 60%
- Optimized indexing queue settings (#665)
  * The biggest overhead is caused by SQL query to fetch products for a single job
  * We lowered the number of jobs to run and increased number of processed products, which reduces the overhead of SQL query
- Refactored `setSettings` method (#675) - **BC Break**
  * Set settings functionality was moved from `Helper\Data` class to newly created `Model\IndicesConfigurator` class
  * Added more information to logging to better understand what settings are pushed to Algolia
- Methods in `Helper\ConfigHelper` class now returns correct types from all its methods

### FIXES

- Fixed IE11 (ES6) compatibility of the extension (#670, #678, #683)
- Fixed displaying of product count in categories in autocomplete menu (#672)
- Fixed issue when it was not possible to select more than 2 attribute values for back-end filtering (#669)
- Fixed failing Visual Category Merchandising tool, when the name of the default store was different from `default` (#674)
- Fixed failing Prevent Backend Rendering feature, when no user-agent was specified in headers (#680)
- Fixed failing `count(null)` call in Category indexer on newer PHP versions (#681)


## 1.9.1

- Fixed `beforeAutocompleteSources` front-end hook to correct pass `sources` (#641)
- Removed legacy `isSearch()` function call from FiltersHelper (#649)
- Updated links to Algolia documentation (#652)
- New design and wording displayed when specific features are not enabled for used Algolia plan (#653)
- Fixed failing price calculation when no child products are assigned to a configurable product (#656)

## 1.9.0

### FEATURES
- Added new "Support page" to find and seek help with the extension (#606)
- Added new "Analytics overview" page to track business performance of the search (#620)
- SEO improvements: (#616)
    - Back-end of the extension now renders the same content as the front-end does on replaced category pages
    - Instant search pages URLs are SEO-friendly

### UPDATES
- The extension no longer supports Magento 2.0 (#588, #594) - **BC Break**
- No query rules are created by default configuration (#599)
- Prevented spoiling of attribute source model by checking if a product attribute uses source (#545)
- When a record is too big t be indexed in Algolia, SKUs of child attributes are truncated (#617)
- Removed "in_stock" condition to display "Add to cart" button on instant search page (#631)

### FIXES
- The Instant Search page and Autocomplete menu configurations are not wiped out when the feature is disabled (#593)
- Fixed price sorting on the front-end with customers group enabled (#625)
- Fixed HTML ID of facet template which was never used (#636)

## 1.8.4

- **Added compatibility with Magento 2.3** (#624)
- When searching for empty string, search results page displayed "__empty__" as searched query. Now it doesn't display anything (#586)
- Fixed failing configuration when query rules are not enabled on Algolia application (#591, #604)
- Removed categories from No results links in autocomplete menu when Categories are not set as attribute for faceting (#592)
- Fixed issue with serialized arrays when Autocomplete or Instant search features are turned off (#593)

## 1.8.3

* Removed the default facet query rule (attribute "color") (#600)

## 1.8.2

* Fixed error which showed Instant search components on checkout page (#572)
* Fixed administration categories and category merchandising on Magento 2.1 (#573)
* Fixed indexing queue page on Magento 2.1 (#575)
* Fixed configurable products' price calculation when parent product has zero price (#580)
* Fixed processed jobs removal from indexing queue (#582)

## 1.8.1

* Fixed PHP 5.5 support (#562)
* Fixed `archive` table creation (#566)
* Fixed PHP notice on not recognised product type (#566)

## 1.8.0

### FEATURES
- Possibility to [reindex specific SKUs](https://community.algolia.com/doc/m2/sku-reindexing-form/) (#536)
    - the form will give an option to reindex specific SKU(s)
    - if the product shouldn't be reindexed, the form shows the exact reason why the product is not indexed 
- Category visual merchandiser - Magento 2.1 and higher (#510)
    - the tool gives possibility to visually merchandise products on category pages powered by Algolia
    - it's placed in category detail in tab "Algolia Merchandising"
- Indexing queue page (#537)
    - The page shows the status and remaining jobs in indexing queue
    - It offers suggestions to improve performance of the queue to get the fastest indexing
- "Non-castable" attributes can now be specified in configuration (#507)
- Added support for tier prices (#558)

### UPDATES
- Configuration page was proofread and enhanced to provide better UX (#526, #529, #531)
- Values of `sku`s and `color`s are now correctly index within record of main configurable product
- Price in filter is correctly formatted (#539)
- Use correct column name (`row_id` vs. `entity_id`) based on staging module availability (#544)
- Improved `algolia_after_products_collection_build` event to pass all relevant parameters (#546)
- The extension has improved [Continuous Integration build](https://github.com/algolia/algoliasearch-magento-2/blob/master/.github/CONTRIBUTING.md) checking quality of code, coding standards and tests (#557)
- Refactored price calculation class (#558)

### FIXES
- Fixed incorrect replacement of "+" and "-" of toggle button of facet panel (#532)
- Fixed indexed URLs for CMS pages (#551)

## 1.7.2

Fixed JavaScript issue causing malfunctioning the extension in IE 11 (#538)

## 1.7.1

### UPDATES
- Algolia JS bundle were updated to it's latest version (#504)

### FIXES
- Fixed issue where configurable products were indexed with "0" prices (#527)
- The extension doesn't throw a fatal error when Algolia credentials are not provided (#505)
- Catalog rule's prices are now correctly indexed within configurable products (#506)
- Scope is correctly added to URLs (#509, #513)

## 1.7.0

### FEATURES
- [Click & Conversion analytics](https://www.algolia.com/doc/guides/analytics/click-analytics/) support (#435, #468, #486, #498) - [Documentation](https://community.algolia.com/magento/doc/m2/click-analytics/)
- Option to automatically create ["facet" query rules](https://www.algolia.com/doc/guides/query-rules/query-rules-overview/?language=php#dynamic-facets--filters) (#438)
- Extension now supports upcoming Algolia's A/B testing feature (#492)

### UPDATES
- Frontend event hooks mechanism was refactored to support multi event listeners (#437) - [Documentation](https://community.algolia.com/magento/doc/m2/frontend-events/)
- Refactoring of code to be more robust and reliable (#436)
- Product is updated in Algolia on it's stock status update (#443)
- Product thumbnail size is now configurable via `etc/view.xml` file (#448)
- `SKU`, `name`, `description` products' attributes are not casted from string (#483)
- Parent product of update child product is now always reindexed (#482)
- `EMPTY_QUEUE` constant name was replaced by more descriptive `PROCESS_FULL_QUEUE` name (#491)
- Refactored `CategoryHelper` to remove memory leak (#495)
- Expired special prices are now replaced by default prices even without reindex (#499)
- [InstantSearch.js library](https://community.algolia.com/instantsearch.js/) was updated to it's latest version bringing [routing feature](https://community.algolia.com/instantsearch.js/v2/guides/routing.html) to the extension (#500)
- Added link to Algolia configuration directly to "Stores" panel (#501)

### FIXES
- Extension now correctly removes disable products from Algolia (#447)
- Fixed the issue when some records weren't indexed because of too big previous record (#451)
- Fixed issue when product was not added to cart on first attempt after page load (#460)
- Removed filenames with asterisks which prevented the extension from being installed on Windows (#461)
- Fixed issue which fetched from DB not relevant sub products (#462)
- Fix issues with wrong category names (#467)
- Fixed issue when backend rendering was prevented not only on category pages (#471)
- Pages from disabled stores are not indexed anymore (#475)
- Fixed image types IDs to configure image sizes via `etc/view.xml` file (#478)
- Fixed exploding of line breaks on User Agents setting for Prevent backend rendering feature to work on Windows servers (#479)
- Correct default values for query suggestions (#484)
- TMP index is now not removed with not used replica indices (#488)
- Fixed documentation links (#490)
- Fixed issue which overrode instant search search parameters (#501)

## 1.6.0

### FEATURES
- New indexer which deletes all products which shouldn't be indexed in Algolia (#405)
- Facets now support [**search for facet values**](https://www.algolia.com/doc/api-reference/api-methods/search-for-facet-values/) feature (#408)
- The extension now displays the right image for a color variant depending on search query or selected color filter (#409)
- Experimental feature to prevent backend rendering of category and search results pages (#413)
    - Use very carefully and read [documentation](https://community.algolia.com/magento/doc/m2/prevent-backend-rendering/) before enabling it
- Infinite scrolling on instant search pages (#414)
- Replica indices are automatically deleted when removing sorting options in configuration (#430)

### UPDATES
- Code is now more readable - **BC Break**
    - shorter lines (#402)
    - lower cyclomatic complexity (#410)
- Price calculation was moved to separate class (#411) - **BC Break**
- Most of `protected` class members were changed to `private` ones (#415) - **BC Break**
- Ranking formula of replicas now contain `filters` rule (#419)
- It's now possible to remove autocomplete menu sections by specifying 0 results in configuration (#429)

### FIXES
- Fixed buggy behavior on iOS when scrolling in autocomplete was not possible (#401)
- Fixed magnifying glass icon next to query suggestions (#403)
- Fixed URL of image placeholders (#428)

## 1.5.0

### FEATURES
- Added option to index empty categories (#382)
- Travis CI now correctly runs builds from community pull requests (#383, #384)
- **BC Break** - Instant search page is now powered by InstantSearch.js v2 (#394)
    - Migration guide: https://community.algolia.com/instantsearch.js/v2/guides/migration.html
    - Magnifier glass and reset search icons are now added directly by ISv2 - old were removed
    - Some template variables were changed (see migration guide)
    - CSS for slider was refactored
- The extension code is checked by [PHPStan](https://github.com/phpstan/phpstan) (#396)

### UPDATES
- Products' and categories' collections now uses `distinct` to fetch only unique records (#371)
- SKUs, names and descriptions are not casted from string to numeric types (#375)
- Configurable product is hidden from search when all its variants are out of stock and out of stock products shouldn't be displayed (#378)
- Stock Qty is not fetched with inner query in collection, but with StockRegistry (#386)
- Indexing jobs for disabled stores are not added to queue anymore (#392)
- **BC Break** - `BaseHelper` class was completely removed (#388, #390)
    - Entity helpers are not dependent on any base class
    - Indexer names can be get from `Data` helper now

### FIXES
- Query suggestions are correctly escaped when displayed (#373)
- Fixed error when `in_stock` comes from `$defaultData` (#374)
- Grouped products now correctly display price ranges (#377)
- The extension now correctly deletes out of stock products from Algolia (#381)
- **BC Break** - Fixed fetching of group ID on frontend (#365)
- Original prices is now displayed correctly with customer groups enabled (#391, #398, #399)
- Cart icon is now clickable on mobile devices (#395, #397)

## 1.4.0

### FEATURES
- When a record is too big to be indexed in Algolia the description displays which attribute is the longest and why the record cannot be indexed (#367)

### UPDATES
- Algolia configuration menu was moved lower (#329)
- Optimized TravisCI (#335)
- More restricted search adapter (#357)
- Indexed product URLs now will never contain SID (#361)

### FIXES
- Fixed price calculations (#330)
- Fixed instant search page with no results - now it displays better "no results" message (#336)
- Fixed attributes to retrieve (#338)
- Fixed `unserialize` method for Magento 2.2 (#339)
- Fixed undefined array index `order` (#340)
- Fixed buggy hover on in autocomplete menu on iOS devices (#351)
- Fixed issue with mixed facets and sorts labels (#354)
- Fixed special prices for customer groups (#359)
- Fixed categories fetching (#366)

## 1.3.0

Since this release, the extension is **Enterprise Edition compliant**!

### FEATURES
- Support of **Magento 2.2** (#319)
- Processing of queue runner is now logged in `algoliasearch_queue_log` table (#310)
- Enabled selection of autocomplete items (#316)

### UPDATES
- Refactored ConfigHelper - removed unused methods (#317)

### FIXES
- API is not called on a non-product page (#311)
- Query rules are not erased on full reindex with queue enabled (#312)

## 1.2.1

- Added configuration option to turn on debug regime for autocomplete menu (#281)
- Fixed the infinite loop in queue processing when ran with `EMPTY_QUEUE=1` (#286)
- Fixed PHP notice on reindex / save settings when `categories` attribute was missing from attributes to index (#293)
- Products with visibility set to `catalog` only are still indexed to show them on category pages (#294)
- Fixed issue which indexed categories within products which shouldn't be displayed in menu (#295)
- Optimized `getPopularQueries` method (#297)
- Fixed issue with missing config default values on Magento 2.1.0 and higher (#300)

## 1.2.0

### FEATURES

- Analytics - the extension now uses Magento's GA to measure searches (#253)
    - [Documentation](https://community.algolia.com/magento/doc/m2/analytics/)
- Option to send an extra Algolia settings to Algolia indices (#245)
- The configuration page now displays information about the indexing queue and gives possibility to clear the queue (#262)
- In attribute select boxes (sorts, facets, ranking, ...) is now possible to choose from all attributes and not just those set as "attribute to indexing" (#257)
- Option to disable synonyms management in Magento (#260)
    - By default it's turned off - if you're using synonyms management in Magento, please turn it on after the upgrade
- Extension back-end events are now more granular (#266)
    - [Documentation](https://community.algolia.com/magento/doc/m2/backend/)

### UPDATES

- All CSS selectors were prefixed with Algolia containers and unused styles were removed (#246)
    - **BC break** - please check the look & feel of your results
- Algolia settings are now pushed to Algolia after the save of extra settings configuration page (#258)
- Added titles to configuration sections (#259)
- **BC Break** - Unused "deleteIndices" method were removed (#263)


### FIXES

- Fix the issue with Algolia error when more than 1000 products are supposed to be deleted (#249)
- Fixed the thumbnail URL when using `/pub/` directory as the root directory (#247)
    - [Documentation](https://community.algolia.com/magento/faq/#in-magento2-the-indexed-image-urls-have-pub-at-the-beginning)
- Fix the issue when backend was still enabled even though it was set as disabled in configuration (#256)
- Fix the issue when indexing was disabled, but the extension still performed some indexing operations (#261)
- Fix category fetching on Magento EE (#265)
- Fix the back button on category pages to not return all products from the store (#267)
- CMS pages are no longer index when the "Pages" section is removed from Addition sections (#271)

## 1.1.0

- Fixed products prices - now all prices (currencies, promos, ...) are correctly indexed (#233)
- Optimized the number of delete queries (#209)
- Image URLs are indexed without protocol (#211)
- Queue processing is now optimized and process always the right number of jobs (#208)
- Fixed the autocomplete menu on mobile (#215, #222)
- Fixed the replica creation with customers' groups enabled (#217)
- Fixed broken reference on Magento_Theme (#224)
- Fix for overloaded queued jobs (#229, #228)
- Fixed encoding of CMS pages (#227)
- Fixed image URLs with double slashes (#234)
- Fixed `attributesToRetrieve` to contain category attributes (#235)

**BC Breaks**
- Refactored `configuration.phtml` - all the logic moved to `Block` class (#238)
- Optimized CSS and assets - removed couple of images and CSS classes (#236)
- JS hooks - instantsearch.js file was completely refactored to create IS config object which can be manipulated via hook method (#240)

## 1.0.10

- Fix IS on replaced category page (#202)

## 1.0.9

- `algoliaBundle` is now loaded only via requireJS (#171)
- Fixed warning raised by nested emulation (#175)
- Fixed indexing of secured URLs (#174)
- Fixed the issue when some products were not indexed due to bad SQL query (#181)
- `categories` attribute is automatically set as an attribute for faceting when Replace categories is turned on (#184)
- Fixed set settings on TMP indices (#186)
- Settings now sends `searchableAttributes` instead of `attributesToIndex` (#187)
- Fixed IS page when using pagination and refreshing the page (#195)
- Fixed filters and pagination on category pages (#200)

## 1.0.8

- Fixed the requireJS issue introduced in 1.0.6, which ended up in administration's JavaScript not working (#168, #169)

## 1.0.6

- Fixed indexing of out-of-stock products (#142)
- Fixed CSS for showing products's ratings on instant search page (#143)
- The category refinement is now displayed in Current filters section and it's easy to remove on replaced category page (#144)
- Fixed indexing of prices with applied price rules (#145, #160)
- Formatted default original price attribute is now retrieved from Algolia by default (#151)
- Fixed showing of "Add to cart" button (#152)
- Exception is not thrown anymore from Algolia indexers when Algolia credentials are not filled in (#155)
    - Fixes the installation abortion when the extension was installed before Magento's installation
- Fixed the layout handle to load templates correctly to Ultimo theme (#156)
- Fixed admin JavaScript to load correctly and not conflict with other pages (#159, #162)
- `script` and `style` tags are now completely (with it's content) removed from CMS pages' indexed content (#163)
- New version of instantsearch.js and autocomplete.js libraries (#165)

## 1.0.5

- Official support of Magento >= 2.1 (#117)
- Fixed method signature for delete objects in Algolia (#120)
- Show all configuration options in website and store views (#133)
- Option "Make SEO Request" is enabled by default now (#134)
- CMS pages are now indexed correctly for specific stores (#135)
- Product's custom data now contains it's `type` in `algolia_subproducs_index` event (#136)
- Replica indices are now not created when Instant Search feature is not enabled (#137)
- New Algolia logo is used in autocomplete menu (#138)
- The extension now sends `replicas` index setting instead of `slaves` (#139)
- Products are now indexed correctly into all assigned stores (#140)

## 1.0.4

- Fixed User-Agent from Magento 1 to Magento 2 (#92)
- Fixed additional sections' links in autocomplete menu (#93)
- All searchable attributes are set as Unordered by default (#94)
- Fixed configHelper to use ProductMetadataInterface to get the correct dependecy (#97)
- Fixed indexing of categories when option "Show categories that are not included in the navigation menu" weren't taken into account (#100)
- Fixed backend Algolia adapter to correctly respect "Make SEO request" configuration (#101)
- Fixed images config paths (#104)
- Added specific HTML classes to refinement widget containers (#105)
- Fixed the issue when queue runner didn't process any job after specific settings changed. Now it process always at least one job (#106)
- Fixed the functionality of "Add To Cart" button (#107)
- Attribute `in_stock` is now exposed in Algolia configuration and can be used for custom ranking or sorting (#109)
- Add `algolia_get_retrievable_attributes` custom event to `getRetrievableAttributes` method to allow developers set custom retrievable  attributes for generated API keys (#112)
- Fixed queue issue when `store_id` parameter was not passed to `deleteObjects` categories' operation (#113)

## 1.0.3

- Fixed issue with indexing content on Magento 2.1 EE and higher (#87)
- Fixed page indexing to index pages only from active stores (#82)

## 1.0.2

- Fixed issue with merging JS files in administration - added new line at the end of [algoliaAdminBundle.min.js](https://github.com/algolia/algoliasearch-magento-2/blob/master/view/adminhtml/web/algoliaAdminBundle.min.js)

## 1.0.1

- Fixed issue with merging JS files - added new line at the end of [algoliaBundle.min.js](https://github.com/algolia/algoliasearch-magento-2/blob/master/view/frontend/web/internals/algoliaBundle.min.js)
- Fixed page indexing when some excluded pages were set
- Fixed data types of `enabled` variables in `algoliaConfig`
- Fixed few typos

## 1.0.0

- Release

## 0.9.1

- Remove `debug: true` from autocomplete menu

## 0.9.0

- Optimized front-end (#54) - **BC break!** 
    - Only necessary JS configuration is now rendered into HTML code, all other JS code is now loaded in within JS files
    - Templates were re-organized to make it's structure more readable
    - Layout's XML files were rewritten and optimized
    - Extension's assets were removed and replaced by SVGs
- Fixed CSS of autocomplete menu's footer (#55, #58)
- Instantsearch.js library was updated to it's latest version (#56)
- The extension officially supports only 2.0.X versions of Magento, however it's still possible and encouraged to use it on 2.1.0 (#53)
- Fixed some annotations in code (#52, #51)

## 0.8.4

- Always index categories' attribute `include_in_menu`
- Follow Magento 2 coding styles

## 0.8.3

- Add license information to `composer.json`

## 0.8.2

- Fix fatal error thrown on Algolia search Adapter on version ~2.0.0
- Fix version in `composer.json`

## 0.8.1

- Fix fatal error thrown when "Make SEO request" was turned on
- Follow the new Algolia's UA convention

## 0.8.0

Initial stable release

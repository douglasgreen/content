## When to Use

Use for data that is:
* Hierarchical
* Closed set

Examples of data that fits the paradigm:
* Content
* Products

Don't use for data that is:
* Non-hierarchical
* Open set

Examples of data that doesn't fit the paradigm:
* Customers
* Orders
* Activty Logs

## Tree Copying Algorithm

- **Content Structure**: The content is structured as a tree, represented by a
  hierarchy of XML documents.
- **XML Document Details**:
  - Each XML document contains **attributes**, some of which may include **UUIDs**.
  - These UUIDs serve as pointers to other XML documents, establishing
    **relationships** between them.
- **Objective**: Develop an algorithm to copy entire trees of XML documents
  along with their interconnecting relationships.
- **Copying Methodology**:
  - The copying process involves transferring the documents **one group at a time**.
  - Each XML document encompasses a list of UUIDs that reference both its
    **parent document** and any **related documents**.
- **Preconditions for Copying**:
  - An XML document can only be copied **after** all its associated parent and
    relationship documents (as identified by UUIDs) have been marked as copied.
- **Date Checking**:
  - Check date when syncing items to see if they need updating.

## Custom SQL queries

Custom SQL SELECT queries can be passed to the content layer to retrieve a set
of UUIDs and their XML documents.

## UUIDs

UUIDs are all defined as attributes `uuid="..."`.

## Deleting Content

Content can only be deleted if there are no references to its UUID.

## Naming Constraints

Names for any schema's entities can be constrained with a regexp like `name-match="..."`.

## Object-Relational Mapping

ORM is easier because XML is rendered directly as objects.

## Database Stability

The database layer becomes stable and doesn't change with changing content
definitions.

## Name-Only Nodes

It's possible to define nodes as name only containers like directories in a file
system that don't have associated XML documents. Just allow the XML to be null.

## One Table

When you split the table into multiple tables, then you can't have consistent
UUID references for all content.

## Combining Databases

It should be possible to combine two databases and renumber their UUIDs.

## Namespaced CMS

Create a CMS like WordPress but all content goes into top-level namespaces in DB
and file system. The namespaces are registered. Enables proper cleanup.

## Levels of Generality

1. Spreadsheet - lowest level. No table definitions.
2. SQL - medium level. Table definitions in one complex layer.
3. XML in SQL - high level. Separates concern of definition from manipulation.

XML in SQL is more general.
* Layered architecture where the lower layer (SQL) described how content is
  maniuplated and upper layer (XML) describes how content is defined.Separating
  those concerns is beneficial because it allows manipulation of content
  separate from its definition. 
* SQL defines UUID, unique name, schemas, schema versions, document hierarchy,
  and command queue/log.
* XML defines data types and relationships which are mirrored in SQL.

## Antipatterns

There are ways to define XML in SQL that work worse. These are design anti-patterns.

* No UUID - Isn't portable between servers. Not easy to define relationships.
* No unique name - No meaningful way to refer to content.
* No schema - Content isn't validated. Must parse XML to identify type.
* JSON instead of XML - Doesn't validate. No XPath queries.
* No versioning of schemas - Hard to change data definition.
* No queue/log - Lacks conflict management and auditability.
* Storing too much data - Logs should not be stored here.
* Deleting data - Use archiving instead.
* Saving all versions - Just save significant changes.
* No PHP direct interface - REST APIs are too slow.
* Using database queries directly - Must use interface.

## Hash

Store hash of data to show it wasn't changed? Or no?

## Read-Only

Offer a read-only setting that doesn't allow updates? Or no?

## Database

* XML-specific databases may be best.
* PostgreSQL has better XML support than MySQL.

## Logging

Universal logging format. DB table contains:
* Log type
* XML of log

XML has versioned schemas just like the above. DB table is partitioned or split
into YYYY, YYYYMM, or YYYYMMDD tables.

## Non-XML

What about storing non-XML blobs? That would be more difficult. Could be stored
in external binary table which is OK because not searched.

## Auto versioning

1. Store a change count as integer.
2. Match all words in source and target with each change.
3. Do a diff (all old words not in new and all new words not in old).
4. Add diff count to change count.
5. When change count exceeds a threshold of 20%, automatic version.

## Like filesystem

The unique name is like a filesystem. In fact, storing documents in a hierarchy
is also like a filesystem. The filesystem doesn't care what's in the document
and can copy folders. That is a separation of concerns between document storage
and document definition just like this system.

## Naming elements and attributes

According to [W3Schools](https://www.w3schools.com/xml/xml_elements.asp) should
should only use word characters and underscores when naming attributes and
elements. Avoid hyphen, dot, and colon, and non-English letters.

This also makes valid identifiers in most languages such as PHP.

## PHP processing

1. SimpleXML - DOM parser, reads and writes, supports XPath, doesn't validate
2. DOMDocument - DOM parser, reads and writes, supports XPath, validates
2. XMLParser - SAX parser
3. XMLReader - Pull parser

SimpleXML and DOMDocument are both part of PHP core. Use SimpleXML if you don't
need validation.

## References
* https://en.wikipedia.org/wiki/XML_database
* https://xml.coverpages.org/xmlIntro.html

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

## Database

* XML-specific databases may be best.
* PostgreSQL has better XML support than MySQL.

## References
* https://en.wikipedia.org/wiki/XML_database
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

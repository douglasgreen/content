## Introduction

The Universal Content Staging Database is a flexible content abstraction layer that streamlines
content management by serving as an intermediate data layer between content sources and delivery
platforms. It provides a centralized repository for storing content as XML blobs validated by
schemas. The staging database offers a content API for content producers and consumers to create and
read data, insulating them from the complexity of direct SQL modification of course tables.

## Two-Step Content Production and Deployment Process

This approach splits the content production and deployment process into two steps:

1. Content Definition and Relationship Management:

    - Define content units and their relationships
    - Move and manipulate content units flexibly

2. Content Transformation for Delivery:
    - Transform content into detailed representations in course data tables
    - Rendering to course database tables is performed by the program itself, not by content
      producers

The second step can be eliminated if consumers are reprogrammed to pull their content directly from
the service. This would require them to process data in XML format rather than using direct database
queries.

## Advantages of Separating Content Definition and Delivery

Currently, these steps are combined, which makes redefining content more difficult and error-prone.
Content producers need to know how to render all data formats for all courses, and separate
uploaders are required for the each content type. By separating these steps, content can be defined
and manipulated at a higher level of abstraction. This allows for flexible uploading of any branch
of data between servers without the need for special uploaders. The final step of rendering content
to database tables is handled by the program, reducing the burden on content producers.

## Handling Binary Objects

Binary objects like images, audio, and videos would still be stored as references to external files
outside the system, ensuring that the staging database remains focused on structured content
management.

## Dedicated Content Management System

This system is designed to serve as a dedicated content management system, rather than a
full-fledged course engine. Its primary focus is on providing a lower data abstraction layer for
content management, without being involved in managing user activity or course-specific
functionality. This single-purpose design allows for better optimization and maintainability of the
content management process, as it is not tightly coupled with the complexities of customer
interaction and course-specific logic.

## Key Features

1. **Centralized Storage**: Stores content in a standardized XML format for easy management and
   processing.

2. **UUIDs**: Assigns a unique identifier to each piece of content for direct lookup and
   maintenance.

3. **Flexible Relationships**: Allows establishing relationships between any two pieces of content
   to map audios, videos, MCQs, study units, outlines, and courses.

4. **Standardized XML**: Utilizes standard XML objects to represent data types like MCQs, ensuring
   consistency.

5. **Cross-Course Handling**: Facilitates management of content integration, tracking,
   synchronization and proofing across courses.

6. **Unified Sources**: Enables multiple content sources to upload content in a common format.

7. **Validation and Versioning** Validation and versioning occur in the XML rather than in the SQL.
   This enables more disciplined upgrades without needing database modifications.

## Supported Content Types

Supports courses, study units, outlines, MCQs, video playlists, audios, videos, images, emails,
etc., and can be expanded as needed.

## Integration and API

Integrates with foreign key lookup tables, validation schemas, and an API for processing and
managing content to ensure data integrity and provide a seamless interface.

## Benefits

1. Simplifies transforming content from production to delivery format
2. Enables direct lookup and maintenance of content via UUIDs
3. Provides flexible content organization through relationships
4. Ensures consistency via standardized XML data structures
5. Facilitates cross-course content management
6. Allows easy extension to new content types and relationships

## Database Schema

The Universal Content Staging Database consists of fives tables that allow UUIDs, schema versioning,
relationships and process requests in a command queue:

1. `schemas`: Stores schema names
2. `schema_versions`: Stores versioned XML content for each schema
3. `content`: Stores content metadata and XML
4. `content_relationships`: Stores relationships between content items
5. `command_queue`: Stores incoming requests to modify data then marks them as complete or canceled

```sql
CREATE TABLE schemas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  UNIQUE (name)
);

CREATE TABLE schema_versions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  schema_id INT NOT NULL,
  version INT NOT NULL,
  xml_content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (schema_id) REFERENCES schemas(id),
  UNIQUE (schema_id, version)
);

CREATE TABLE content (
    id BINARY(16) PRIMARY KEY,
    parent_id BINARY(16) NULL,
    name VARCHAR(255) NOT NULL,
    schema_version_id INT NOT NULL,
    is_archived BOOLEAN NOT NULL DEFAULT FALSE,
    inserted_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    content_xml TEXT NOT NULL,
    FOREIGN KEY (parent_id) REFERENCES content(id),
    FOREIGN KEY (schema_version_id) REFERENCES schema_versions(id),
    UNIQUE (parent_id, name),
    INDEX idx_parent_id (parent_id)
);

CREATE TABLE content_relationships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_content_id BINARY(16) NOT NULL,
    target_content_id BINARY(16) NOT NULL,
    FOREIGN KEY (source_content_id) REFERENCES content(id),
    FOREIGN KEY (target_content_id) REFERENCES content(id),
    UNIQUE (source_content_id, target_content_id),
    INDEX idx_source_content_id (source_content_id),
    INDEX idx_target_content_id (target_content_id)
);

CREATE TABLE command_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    command_type ENUM('insert', 'update', 'delete', 'archive', 'copy', 'replace') NOT NULL,
    source_content_id BINARY(16) NOT NULL,
    target_content_id BINARY(16) NULL,
    command_data TEXT NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'canceled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
);
```

## PHP Interfaces and REST API

A PHP class library can be provided for direct and efficient database access. A REST API can be
built on top of the library for access across different languages and servers.

### PHP Interfaces

```php
interface SchemaRepositoryInterface
{
    public function createSchema(string $name): int;
    public function getSchemaByName(string $name): ?array;
}

interface SchemaVersionRepositoryInterface
{
    public function createSchemaVersion(int $schemaId, int $version, string $xmlContent): int;
    public function getSchemaVersionById(int $id): ?array;
    public function getLatestSchemaVersionBySchemaId(int $schemaId): ?array;
}

interface ContentRepositoryInterface
{
    public function createContent(
        string $id,
        ?string $parentId,
        string $name,
        int $schemaVersionId,
        string $contentXml,
    ): void;
    public function getContentById(string $id): ?array;
    public function updateContent(string $id, array $data): void;
    public function archiveContent(string $id): void;
    public function getContentByParentId(string $parentId): array;
}

interface ContentRelationshipRepositoryInterface
{
    public function createContentRelationship(
        string $sourceContentId,
        string $targetContentId,
    ): int;
    public function getContentRelationshipsBySourceId(string $sourceContentId): array;
    public function getContentRelationshipsByTargetId(string $targetContentId): array;
}

interface ContentValidatorInterface
{
    public function validateContent(string $contentXml, string $schemaXml): bool;
}

interface ContentServiceInterface
{
    public function createContent(
        string $name,
        ?string $parentId,
        int $schemaId,
        string $contentXml,
    ): string;
    public function getContentById(string $id): ?array;
    public function updateContent(string $id, array $data): void;
    public function archiveContent(string $id): void;
    public function getContentByParentId(string $parentId): array;
    public function createContentRelationship(
        string $sourceContentId,
        string $targetContentId,
    ): int;
    public function getContentRelationshipsBySourceId(string $sourceContentId): array;
    public function getContentRelationshipsByTargetId(string $targetContentId): array;
}

interface CommandQueueRepositoryInterface
{
    public function createCommand(string $commandType, array $commandData): int;
    public function getCommandById(int $id): ?array;
    public function updateCommandStatus(int $id, string $status): void;
    public function getOldestPendingCommand(): ?array;
}

interface CommandProcessorInterface
{
    public function processCommand(array $command): void;
}
```

### REST API

This REST API provides endpoints for managing schemas, schema versions, content, and content
relationships. It assumes that the required PHP interfaces and their implementations are available
and injected into the API implementation.

The API endpoints follow a RESTful design:

-   Schema endpoints:

    -   `POST /schemas`: Create a new schema
    -   `GET /schemas/{name}`: Get a schema by name

-   Schema version endpoints:

    -   `POST /schemas/{schemaId}/versions`: Create a new schema version
    -   `GET /schemas/{schemaId}/versions/{versionId}`: Get a schema version by ID
    -   `GET /schemas/{schemaId}/versions/latest`: Get the latest schema version for a schema

-   Content endpoints:

    -   `POST /content`: Create new content
    -   `GET /content/{id}`: Get content by ID
    -   `PUT /content/{id}`: Update content
    -   `DELETE /content/{id}`: Archive content
    -   `GET /content?parent_id={parentId}`: Get content by parent ID

-   Content relationship endpoints:

    -   `POST /content/{sourceId}/relationships/{targetId}`: Create a new content relationship
    -   `GET /content/{id}/relationships/source`: Get content relationships by source ID
    -   `GET /content/{id}/relationships/target`: Get content relationships by target ID

-   Command queue endpoints:
    -   `POST /commands`: Create a new command
    -   `GET /commands/{id}`: Get a command by ID
    -   `PUT /commands/{id}/status`: Update a command's status

## Schema and Content Versioning

The Universal Content Staging Database supports two types of versioning: schema versioning and
content versioning. Schema versioning is used when the design of the content structure has changed,
such as adding new fields or modifying existing ones. This type of versioning is handled by the
`schemas` and `schema_versions` tables in the database. The `schemas` table stores the names of the
schemas, while the `schema_versions` table stores the versioned XML content for each schema,
allowing for the tracking of changes over time.

On the other hand, content versioning is used when a new edition of the content has been produced,
such as updating the text or images within a specific content unit. This type of versioning is
handled by adding a version node to the XML tree structure. For example, a content unit for a course
might have a version node like "Course -> 2024" to indicate the 2024 edition of the content. The
version node is stored as an XML blob, just like any other node in the content tree. This approach
allows for the definition and copying of new versions by simply copying branches of the XML tree in
a simple and regular way. By treating the version information as a regular part of the content
structure, the Universal Content Staging Database provides a flexible and consistent method for
managing content versions without the need for separate versioning mechanisms.

## Semantic and Non-Semantic Referencing

The Universal Content Staging Database organizes content using an XML tree structure, which is a
hierarchy of parent-child relationships. The design of the `content` table includes a unique
constraint on the combination of `parent_id` and `name` columns, ensuring that each XML blob has a
unique name within its parent container. This structure allows for content referencing by traversing
the XML tree using a series of name fetches, similar to specifying a directory path.

To reference a specific piece of content, you can use either its global UUID or a unique series of
names that represent the path to the content within the XML tree. For example, to reference a
specific multiple-choice question in a course course, you could use a path like "Course -> 2024 ->
StudyUnit -> MCQ -> Q23". In this path, each name is only unique within its parent context, which
means that the same name can be reused in different parts of the content hierarchy. This allows for
flexible and meaningful organization of content.

Each content unit in the staging database has two types of identifiers: a semantic name and a
non-semantic name. The semantic name is a human-readable representation of the content's location
within the XML tree, such as "Course_2024_StudyUnit_MCQ_Q23". This name provides a clear
understanding of the content's context and makes it easier for content producers and consumers to
work with the content. On the other hand, the non-semantic name is the content's UUID, which is a
globally unique identifier that does not carry any inherent meaning. The UUID is used for efficient
and unambiguous content referencing, especially when dealing with complex content relationships or
when the content needs to be accessed programmatically.

By providing both semantic and non-semantic naming options, the Universal Content Staging Database
offers flexibility in content referencing, allowing users to choose the most appropriate method
based on their specific needs. The ability to traverse the XML tree using a series of names enables
intuitive content organization and retrieval, while the use of UUIDs ensures that each content unit
can be uniquely identified and referenced across the entire system.

## Command Queue

The command queue is a table added to the Universal Content Staging Database that stores and manages
pending commands for modifying the XML hierarchy. Commands such as adding nodes, removing nodes,
archiving nodes, and copying subtrees are stored in the queue with a status of 'pending',
'processing', 'completed', 'failed', or 'cancelled'. A background worker or cron job processes the
commands in the order they were created, executing each command based on its type and data, and
updating the command's status accordingly.

The command queue provides several benefits. It ensures that modifications to the XML hierarchy are
performed in a controlled and orderly manner, preventing conflicting updates. It allows for better
error handling and the ability to cancel or retry failed commands. Additionally, the command queue
facilitates logging because when a command is completed, a record of all changes is stored in the
database. This logging feature enables auditing, tracking, and debugging of the modifications made
to the XML hierarchy over time.

## Why an XML Hierarchy is Better Than Flat SQL Tables

The Universal Content Staging Database uses a uniform hierarchy of XML nodes to store and manage
content. This hierarchical XML structure has several advantages over using traditional flat SQL
tables:

1. True hierarchy: XML natively represents hierarchical relationships, while SQL tables can only
   imitate hierarchies through flat parent-child relationships. The XML hierarchy maps directly to
   the natural structure of the content.

2. Versioning: The XML structure allows for easy versioning of both schemas (structure) and content
   (data). Schemas can be versioned to handle changes in content structure over time. Content
   versioning is achieved by simply adding version nodes to the XML tree. SQL lacks built-in support
   for robust versioning.

3. Higher-level abstraction: XML provides a higher level of abstraction for defining and
   manipulating content compared to low-level SQL tables and queries. This abstraction layer
   insulates content consumers from the underlying storage details.

4. Universal identifiers: Each content unit in the XML hierarchy has a UUID for unique global
   identification and a semantic hierarchical name that represents its location in the content tree.
   SQL tables lack standardized universal identifiers.

5. Uniform interface: The XML hierarchy is accessed and manipulated through a uniform, well-defined
   service interface. SQL tables often require custom accessor methods for each table, leading to
   fragmentation.

6. Command queue: Changes to the XML hierarchy are managed by a command queue that executes, logs,
   and provides auditability of content modifications. SQL lacks a standardized mechanism for
   managing and tracking content changes.

7. Schema definition and validation: The XML structure allows content units to be defined in schema
   files that can be shared across courses. SQL tables are often inconsistently designed across
   courses.

8. Rendering and manipulation: XML's hierarchical structure and schemas make it easier to render
   content units to different formats or manipulate them programmatically. SQL requires piecing
   together content from multiple tables.

9. Flexible relationships: XML enables establishing arbitrary relationships between any two content
   units. With SQL, setting up relationships requires altering table schemas and creating custom
   join queries.

In contrast, a traditional database design using flat rectangular SQL tables has several drawbacks:

-   Cumbersome data validation requiring separate validation logic for each table
-   Tight coupling between storage implementation and content consumer code
-   Lack of robust versioning capabilities for table schemas and content
-   Inconsistent table designs across different content types and courses
-   Inability to universally address content units by UUID or hierarchical name
-   Difficulty in uniformly handling cross-cutting concerns like rendering or proofing
-   Need for schema changes and database migrations to handle content structure changes

In summary, using an XML hierarchy provides a more natural, flexible, maintainable, and evolvable
approach to storing and managing content compared to flat, fragmented SQL tables. The hierarchical
XML structure, combined with the staging database's capabilities, enables efficient content
handling.

## Comparison to a File System

The Universal Content Staging Database's approach of storing an XML hierarchy in a database can be
compared to a file system. In a file system, files are stored in directories, which form a proper
hierarchy. There is a clear separation of concerns between how each file is defined (its content)
and how files are stored (the directory structure). This separation allows files to be freely
copied, moved, or manipulated without needing to know or modify their contents.

In contrast, a traditional database design using plain SQL tables breaks data apart into flat
tables. These flat tables do not naturally represent a hierarchy, and there is no inherent
separation of concerns between how the table is defined (its schema) and how its data is inserted,
updated, or copied. If a file system worked like SQL, there would be no subdirectories, and files
would be stored in a single large pile. A crude system of naming conventions would be used to try to
create a hierarchy of content, and copying files would be a complex process that would have to take
into account the content of each file before copying it.

The Universal Content Staging Database's XML hierarchy storage addresses these issues by:

1. **Proper Hierarchy**: Data is stored in XML documents within a proper hierarchy, similar to files
   in directories. This hierarchy is maintained by the `parent_id` and `name` columns in the
   `content` table.

2. **Separation of Concerns**: There is a clear separation of concerns between the XML that defines
   the data (content definition) and the SQL that stores it (content storage). This separation
   allows content to be freely manipulated, copied, or moved without modifying the XML definition.

3. **Hierarchical Identifiers**: Each content unit has a proper hierarchical ID (UUID) for unique
   global identification and a proper hierarchical name (semantic path) representing its location in
   the content tree, similar to the hierarchical names of files in a file system.

4. **Independent Manipulation**: Content units can be copied, stored, imported, exported, and
   uploaded independently of their XML definition, just like files in a file system can be
   manipulated independently of their content.

By adopting a file system-like approach to storing and managing content, the Universal Content
Staging Database provides a more intuitive, flexible, and maintainable solution compared to
traditional flat SQL tables. This approach enables efficient content organization, retrieval, and
manipulation, making it easier to manage complex content hierarchies and adapt to future changes in
content structure or relationships.

## Comparison to a Moving Service

The Universal Content Staging Database acts as a content abstraction layer, which can be compared to
a moving service. When data is stored directly in databases, it is rendered into individual pieces
for the end user, similar to a collection of items like dishes and books being moved around in
stacks. This process can be complex, error-prone, and may result in damage to fragile items.

Introducing a moving service simplifies the process by packing your belongings into boxes and moving
the boxes instead of individual items. In the same way, the content abstraction layer represents
content as unified blobs of XML rather than scattered database tables. The "moving service" (i.e.,
the staging database) only needs to know how to pack the content into "boxes" (XML blobs) and move
the boxes, without concern for the specific details of each item.

This approach offers several benefits:

1. **Simplicity**: By abstracting the content into a standardized format, the process of moving and
   managing content becomes more straightforward and less complex.

2. **Reduced Errors**: With a unified representation of content, there is less room for errors that
   may occur when dealing with individual pieces of data scattered across different tables.

3. **Scalability**: As new types of content are introduced, they can be easily packed into the same
   "boxes" (XML blobs) and moved using the same service without requiring a new type of object
   movement. This makes the system more scalable and adaptable to future changes.

4. **Consistency**: By using a standardized format for content representation, the moving service
   ensures that all content is handled consistently, reducing the risk of inconsistencies or
   compatibility issues.

The XML of this service addresses the same concern as the different database tables, namely data
definition and validation, but it does so in a more concise and uniform manner. This approach makes
the content tractable to the end program, which can pull content from the database and process it
using standard XML tools without having to build custom data structures out of disparate database
queries.

In summary, the Universal Content Staging Database, like a moving service, simplifies the process of
managing and moving content by abstracting it into a unified format. This approach improves the
overall efficiency, reliability, and scalability of the content management process, making it easier
to handle diverse content types and adapt to future changes.

## Long-Term Integration

The long-term plan for the Universal Content Staging Database is to have all content producers store
their content using this service. This approach will eliminate the need for courses to define their
own database tables for content, simplifying the overall architecture and reducing maintenance
overhead. Courses can fetch content in XML format directly from the service and render it to HTML
using standard XML processing tools, making the content rendering process more efficient and
consistent across different courses.

By adopting this centralized content staging approach, the entire content management ecosystem
becomes more streamlined, with a clear separation between content production and consumption. This
separation allows for easier updates and enhancements to the content management process without
affecting the individual courses. Furthermore, as new content types or producers are introduced,
they can easily integrate with the existing system by adhering to the standardized XML format,
ensuring scalability and extensibility in the long run.

## Conclusion

The Universal Content Staging Database provides a powerful, centralized solution for structuring,
managing and transforming diverse content efficiently. Its flexible design promotes content reuse
and extensibility.

## References

-   [Staging (Data)](<https://en.wikipedia.org/wiki/Staging_(data)>)

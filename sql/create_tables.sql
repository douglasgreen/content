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

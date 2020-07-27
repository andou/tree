CREATE TABLE node_tree
(
    idNode INT AUTO_INCREMENT PRIMARY KEY,
    level  INT NOT NULL,
    iLeft  INT NOT NULL,
    iRight INT NOT NULL
) ENGINE = INNODB;;

CREATE TABLE node_tree_names
(
    idNodeTrans INT AUTO_INCREMENT PRIMARY KEY,
    idNode      INT,
    language    VARCHAR(16)  NOT NULL,
    nodeName    VARCHAR(255) NOT NULL,
    INDEX par_ind (idNode),
    FOREIGN KEY (idNode)
        REFERENCES node_tree (idNode)
        ON DELETE CASCADE
) ENGINE = INNODB;
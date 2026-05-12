<?php

/**
 * Retorna el mapa de taules i camps de la base de dades CAS3.
 *
 * Els noms surten del fitxer local:
 * `Ignora/m898_cas3_create_tables.sql.txt`.
 *
 * @return array Mapa de noms logics cap als noms reals de MariaDB.
 */
function db_schema()
{
    return [ // Retorna la definició completa de l'esquema
        'students' => [ // Entitat d'alumnes
            'table' => 'Alumnes', // Nom real de la taula
            'fields' => [ // Mapeig de camps
                'id' => 'id', // ID d'alumne
                'name' => 'nom', // Nom de pila
                'surname1' => 'cognom1', // Primer cognom
                'surname2' => 'cognom2', // Segon cognom
                'email' => 'correu', // Correu electrònic
                'group' => 'grupClasse', // Grup de classe
            ],
        ],
        'users' => [ // Entitat d'usuaris del sistema
            'table' => 'Usuaris', // Nom real de la taula
            'fields' => [ // Mapeig de camps
                'id' => 'id', // ID d'usuari
                'name' => 'nom', // Nom
                'surname1' => 'cognom1', // Primer cognom
                'surname2' => 'cognom2', // Segon cognom
                'email' => 'correu', // Correu per al login
                'password_hash' => 'contrasenya_hash', // Hash de la contrasenya
                'role' => 'rol', // Rol: PROFESSOR o ALUMNE
                'student_id' => 'idAlumne', // Relació amb Alumnes si és el cas
                'active' => 'actiu', // Estat del compte
                'created_at' => 'creatEl', // Data de creació
            ],
        ],
        'locations' => [ // Entitat d'ubicacions
            'table' => 'Ubicacions', // Nom real de la taula
            'fields' => [ // Mapeig de camps
                'id' => 'id', // ID d'ubicació
                'name' => 'nom', // Nom de l'aula o espai
            ],
        ],
        'material_types' => [ // Entitat de tipus de material
            'table' => 'TipusMaterial', // Nom real de la taula
            'fields' => [ // Mapeig de camps
                'id' => 'id', // ID del tipus
                'type' => 'tipus', // Nom del tipus de material
                'model' => 'model', // Model del dispositiu
                'origin' => 'origen', // Origen del material
            ],
        ],
        'material' => [ // Entitat de material (inventari)
            'table' => 'Material', // Nom real de la taula
            'fields' => [ // Mapeig de camps
                'id' => 'id', // ID de l'objecte
                'type_id' => 'idTipus', // Relació amb el tipus
                'inventory_id' => 'idInventari', // Codi d'inventari
                'department_label' => 'etiquetaDepInf', // Etiqueta del departament
                'serial_number' => 'numSerie', // Número de sèrie
                'mac_ethernet' => 'macEthernet', // Adreça MAC Ethernet
                'mac_wifi' => 'macWifi', // Adreça MAC Wi-Fi
                'sace' => 'SACE', // Codi SACE
                'acquisition_date' => 'dataAdquisicio', // Data de compra/arribada
                'location_id' => 'idUbicacio', // Relació amb la ubicació actual
            ],
        ],
        'assignments' => [ // Entitat d'assignacions de material
            'table' => 'Assignacions', // Nom real de la taula
            'fields' => [ // Mapeig de camps
                'id' => 'id', // ID de l'assignació
                'material_id' => 'idMaterial', // Relació amb el material
                'student_id' => 'idAlumne', // Relació amb l'alumne
                'start_date' => 'dataInici', // Data de lliurament
                'end_date' => 'dataFinal', // Data de devolució
            ],
        ],
        'states' => [ // Entitat d'estats d'incidència
            'table' => 'Estats', // Nom real de la taula
            'fields' => [ // Mapeig de camps
                'id' => 'id', // ID de l'estat
                'status' => 'estat', // Nom de l'estat (Obert, Tancat, etc.)
            ],
        ],
        'incidents' => [ // Entitat d'incidències
            'table' => 'Incidencies', // Nom real de la taula
            'fields' => [ // Mapeig de camps
                'id' => 'id', // ID de la incidència
                'info' => 'informacio', // Descripció del problema
                'opened_at' => 'dataOberta', // Data d'obertura
                'closed_at' => 'dataTancada', // Data de tancament
                'student_id' => 'idAlumne', // Alumne que la reporta
                'device_id' => 'idDispositiu', // Material afectat
                'state_id' => 'idEstat', // Estat actual de la incidència
            ],
        ],
    ];
}

/**
 * Protegeix un nom de taula o columna abans d'usar-lo dins SQL.
 *
 * @param string $name Nom real de la taula o columna.
 * @return string Nom protegit amb accents invertits.
 */
function db_ident($name)
{
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) { // Valida que el nom només contingui caràcters alfanumèrics i guions baixos
        throw new RuntimeException('Nom de BD no valid: ' . $name); // Llança error si es detecta un possible intent d'injecció o caràcters no vàlids
    }

    return '`' . $name . '`'; // Retorna el nom entre accents invertits per complir amb la sintaxi MariaDB
}

/**
 * Retorna el nom real d'una taula del mapa.
 *
 * @param string $name Nom logic de la taula.
 * @return string Nom real protegit amb accents invertits.
 */
function schema_table($name)
{
    $schema = db_schema(); // Carrega la definició completa de l'esquema

    return db_ident($schema[$name]['table']); // Identifica el nom real i el protegeix per a SQL
}

/**
 * Retorna el nom real d'un camp del mapa.
 *
 * @param string $table Nom logic de la taula.
 * @param string $field Nom logic del camp.
 * @return string Nom real protegit amb accents invertits.
 */
function schema_field($table, $field)
{
    $schema = db_schema(); // Carrega la definició completa de l'esquema

    return db_ident($schema[$table]['fields'][$field]); // Busca el nom real del camp i el protegeix per a SQL
}

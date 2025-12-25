<?php

include_once("AccessBDD.php");

/**
 * Classe MyAccessBDD
 * hérite de AccessBDD et redéfinit les fonctions abstraites
 */
class MyAccessBDD extends AccessBDD
{
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct()
    {
        try {
            parent::__construct();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */
    protected function traitementSelect(string $table, ?array $champs): ?array
    {
        switch ($table) {
            case "":
                // return $this->uneFonction(parametres); // cas spécifique si besoin
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }
    }

    /**
     * insert dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de lignes affectées ou null si erreur
     * @override
     */
    protected function traitementInsert(string $table, ?array $champs): ?int
    {
        if (empty($champs)) {
            return null;
        }

        $colonnes = implode(", ", array_keys($champs));
        $placeholders = ":" . implode(", :", array_keys($champs));
        $requete = "INSERT INTO $table ($colonnes) VALUES ($placeholders)";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * update dans une table
     * @param string $table
     * @param string|null $id
     * @param array|null $champs
     * @return int|null nombre de lignes affectées ou null si erreur
     * @override
     */
    protected function traitementUpdate(string $table, ?string $id, ?array $champs): ?int
    {
        if (empty($id) || empty($champs)) {
            return null;
        }

        $setClause = "";
        foreach ($champs as $key => $value) {
            $setClause .= "$key = :$key, ";
        }
        $setClause = substr($setClause, 0, -2); // enlever la dernière virgule

        $requete = "UPDATE $table SET $setClause WHERE id = :id";
        $champs["id"] = $id;

        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * delete dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de lignes affectées ou null si erreur
     * @override
     */
    protected function traitementDelete(string $table, ?array $champs): ?int
    {
        if (empty($champs)) {
            return null;
        }

        $whereClause = "";
        foreach ($champs as $key => $value) {
            $whereClause .= "$key = :$key AND ";
        }
        $whereClause = substr($whereClause, 0, -5); // enlever le dernier " AND "

        $requete = "DELETE FROM $table WHERE $whereClause";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null
     */
    private function selectTuplesOneTable(string $table, ?array $champs): ?array
    {
        if (empty($champs)) {
            // tous les tuples d'une table
            $requete = "SELECT * FROM $table;";
            return $this->conn->queryBDD($requete);
        } else {
            // tuples spécifiques d'une table
            $requete = "SELECT * FROM $table WHERE ";
            foreach ($champs as $key => $value) {
                $requete .= "$key = :$key AND ";
            }
            // enlever le dernier " AND "
            $requete = substr($requete, 0, -5);
            return $this->conn->queryBDD($requete, $champs);
        }
    }
}

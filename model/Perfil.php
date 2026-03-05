<?php
require_once(__DIR__ . "/../config/conexao.php");
 
 
class Perfil
{
    private int     $id;
    private string  $nome;
 
 
    public function __construct(
        int $id,
        string $nome
    ) {
        $this->id = $id;
        $this->nome = $nome;
    }
 
    // Métodos mágicos Get e Set
    public function __get(string $prop)
    {
        if (property_exists($this, $prop)) {
            return $this->$prop;
        }
        throw new  Exception("Propriedade {$prop} não existe");
    }
 
    public function __set(string $prop, $valor)
    {
        switch ($prop) {
            case "id":
                $this->id = (int)$valor;
                break;
            case "nome":
                $this->nome = trim($valor);
                break;
            default:
                throw new Exception("Propriedade {$prop} não permitida");
        }
    }
 
    private static function getConexao()
    {
        return (new Conexao())->conexao();
    }
 
 
    public static function listar()
    {
        $pdo = self::getConexao();
 
        $sql = "SELECT * FROM `perfis`";
 
        $stmt = $pdo->query($sql);
 
        // Retornando todos os usuarios
        $perfis = [];
 
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $perfil = new Perfil(
                id: $row['id_perfil'],
                nome: $row['nome_perfil'],
            );
            array_push($perfis, $perfil);
        }
 
        return $perfis;
    }
 
    public static function listarPorId(int $id)
    {
        $pdo = self::getConexao();
 
        $sql = "SELECT * FROM   `perfis` WHERE `id_perfil`=:id";
 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
 
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        if (!$row) {
            throw new Exception("ID do usuário não encontrado.");
            return null;
        }
 
        $perfil = new Perfil(
            id: $row['id_perfil'],
            nome: $row['nome_perfil'],
        );
 
 
        return $perfil;
    }
}
 
 
print_r(Perfil::listarPorId(1));
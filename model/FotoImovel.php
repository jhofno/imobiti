<?php
require_once(__DIR__ . "/../config/conexao.php");

class FotoImovel
{
    // Propriedades privadas baseadas no diagrama
    private ?int $id_foto;
    private int $id_imovel;
    private string $caminho;
    private bool $destaque;
    private int $ordem;

    public function __construct(
        ?int $id_foto = 0,
        int $id_imovel = 0,
        string $caminho = "",
        bool $destaque = false,
        int $ordem = 0
    ) {
        $this->id_foto = $id_foto;
        $this->id_imovel = $id_imovel;
        $this->caminho = $caminho;
        $this->destaque = $destaque;
        $this->ordem = $ordem;
    }

    // Métodos mágicos Get e Set seguindo o padrão do seu projeto
    public function __get(string $prop)
    {
        if (property_exists($this, $prop)) {
            return $this->$prop;
        }
        throw new Exception("Propriedade {$prop} não existe");
    }

    public function __set(string $prop, $valor)
    {
        if (property_exists($this, $prop)) {
            switch ($prop) {
                case "id_foto":
                case "id_imovel":
                case "ordem":
                    $this->$prop = (int)$valor;
                    break;
                case "destaque":
                    $this->$prop = (bool)$valor;
                    break;
                default:
                    $this->$prop = trim($valor);
                    break;
            }
        } else {
            throw new Exception("Propriedade {$prop} não existe");
        }
    }

    private static function getConexao()
    {
        return (new Conexao())->conexao();
    }

    // Método para Salvar a foto no banco de dados
    public function salvar()
    {
        $pdo = self::getConexao();
        
        // SQL direcionado para a tabela fotos_imovel
        $sql = "INSERT INTO `fotos_imovel` (id_imovel, caminho, destaque, ordem) 
                VALUES (:id_imovel, :caminho, :destaque, :ordem)";

        $stmt = $pdo->prepare($sql);
        
        $res = $stmt->execute([
            ':id_imovel' => $this->id_imovel,
            ':caminho'   => $this->caminho,
            ':destaque'  => $this->destaque ? 1 : 0, // tinyint(1) no banco
            ':ordem'     => $this->ordem
        ]);

        if ($res) {
            $this->id_foto = (int)$pdo->lastInsertId();
        }
        
        return $res;
    }

    // Método para buscar fotos por imóvel
    public static function buscarPorImovel(int $idImovel)
    {
        $pdo = self::getConexao();
        $sql = "SELECT * FROM `fotos_imovel` WHERE id_imovel = :id ORDER BY ordem ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idImovel]);

        $fotos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fotos[] = new FotoImovel(
                id_foto:  $row['id_foto'],
                id_imovel: $row['id_imovel'],
                caminho:   $row['caminho'],
                destaque:  (bool)$row['destaque'],
                ordem:     $row['ordem']
            );
        }
        return $fotos;
    }

    // Método solicitado no diagrama
    public function reordenar(int $novaOrdem)
    {
        $this->ordem = $novaOrdem;
        $pdo = self::getConexao();
        $sql = "UPDATE `fotos_imovel` SET ordem = :ordem WHERE id_foto = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':ordem' => $novaOrdem, ':id' => $this->id_foto]);
    }
}
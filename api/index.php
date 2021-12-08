<?php
include "Conexao.php";
$db = Conexao::getConexao();

if (isset($_GET["path"])) {
    $path = rtrim($_GET["path"], "/");
    $path = explode("/", $path);

    if (isset($path[1])) {
        $parametro = $path[1];
    }
}

header('Content-Type: application/json');

function getRequest() {
    if (file_get_contents("php://input") != null and !empty(file_get_contents("php://input"))) {
        if ($request = json_decode(file_get_contents("php://input"), true)) {
            $return["status"] = "ok";
            $return["request"] = $request;
        } else {
            $return["status"] = "erro";
            $return["mensagem"] = "Request não está em JSON";
        }
    } else {
        $return["status"] = "erro";
        $return["mensagem"] = "Request está vazia";
    }
    return $return;
}


if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (isset($parametro)) {
        $query = $db->query("SELECT * FROM livro WHERE id=$parametro");
    } else {
        $query = $db->query("SELECT * FROM livro");
    }
    while ($item = $query->fetch(PDO::FETCH_ASSOC)) {
        $livro["id"] = $item["id"];
        $livro["titulo"] = $item["titulo"];
        $livro["autor"] = $item["autor"];
        $livro["editora"] = $item["editora"];
        $livro["ano_publicacao"] = $item["ano_publicacao"];
        $livro["ISBN"] = $item["ISBN"];
        $retorno[] = $livro;
    }
    echo json_encode($retorno);
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $request = getRequest();
    if ($request["status"] != "ok") {
        $retorno = $request;
    } else {
        $request = $request["request"];
        if (isset($request["titulo"]) && isset($request["autor"]) && isset($request["editora"]) && isset($request["ano_publicacao"]) && isset($request["ISBN"])) {
            $titulo = $request["titulo"];
            $autor = $request["autor"];
            $editora = $request["editora"];
            $ano_publicacao = $request["ano_publicacao"];
            $ISBN = $request["ISBN"];
           
            $sql = "INSERT INTO livro(titulo, autor, editora, ano_publicacao, ISBN) VALUES (\"$titulo\",\"$autor\",\"$editora\",\"$ano_publicacao-01-01\",\"$ISBN\")";

            if ($db->query($sql)) {
                $retorno = $request;
                $retorno["id"] = $db->lastInsertId();
            } else {
                $retorno["status"] = "erro";
                $retorno["mensagem"] = "erro ao inserir dado";
            }
        } else {
            $retorno["status"] = "erro";
            $retorno["mensagem"] = "nem todos os parâmetros foram especificados";
        }
    }
    echo json_encode($retorno);
} else if ($_SERVER['REQUEST_METHOD'] == "PUT") {
    $request = getRequest();
    if ($request["status"] == "ok") {
        if (isset($parametro) && is_numeric($parametro) && $parametro > 0) {
            $request = $request["request"];
            if (isset($request["titulo"]) && isset($request["autor"]) && isset($request["editora"]) && isset($request["ano_publicacao"]) && isset($request["ISBN"])) {
                $titulo = $request["titulo"];
                $autor = $request["autor"];
                $editora = $request["editora"];
                $ano_publicacao = $request["ano_publicacao"];
                $ISBN = $request["ISBN"];
                $sql =  "UPDATE livro SET titulo=\"$titulo\", autor=\"$autor\", editora=\"$editora\", ano_publicacao=\"$ano_publicacao-01-01\", ISBN=\"$ISBN\" WHERE id=$parametro";
                if ($db->query($sql)) {
                    $retorno = $request;
                    $retorno["id"] = $parametro;
                } else {
                    $retorno["status"] = "erro";
                    $retorno["mensagem"] = "erro ao alterar dado";
                }
            } else {
                $retorno["status"] = "erro";
                $retorno["mensagem"] = "nem todos os parâmetros foram especificados";
            }
        } else {
            $retorno["status"] = "erro";
            $retorno["mensagem"] = "erro no parametro especificado";
        }
    } else {
        $retorno = $request;
    }
    echo json_encode($retorno);
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
    if (isset($parametro) && is_numeric($parametro) && $parametro > 0) {
        if ($query = $db->query("SELECT * FROM livro WHERE id=$parametro")) {
            while ($item = $query->fetch(PDO::FETCH_ASSOC)) {
                $livro["id"] = $item["id"];
                $livro["titulo"] = $item["titulo"];
                $livro["autor"] = $item["autor"];
                $livro["editora"] = $item["editora"];
                $livro["ano_publicacao"] = $item["ano_publicacao"];
                $livro["ISBN"] = $item["ISBN"];
                $retorno[] = $livro;
            }
            if (!$db->query("DELETE FROM livro WHERE id=$parametro")) {
                $retorno["status"] = "erro";
                $retorno["mensagem"] = "erro ao deletar dados";
            }
        } else {
            $retorno["status"] = "erro";
            $retorno["mensagem"] = "erro ao ler dados";
        }
    } else {
        $retorno["status"] = "erro";
        $retorno["mensagem"] = "erro no parametro especificado";
    }
    echo json_encode($retorno);
}

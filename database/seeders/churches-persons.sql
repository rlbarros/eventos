SELECT i.nome AS church,
    e.id AS state_id,
    c.id AS city_id,
    f.nome as function,
    p.cpf as cpf,
    p.nome AS name,
    p.telefone AS phone,
    p.data_de_nascimento AS birth_date
FROM alocacoes_ministros am
    INNER JOIN pessoas p ON am.pessoa_id = p.id
    INNER JOIN igrejas i ON am.igreja_id = i.id
    INNER JOIN superintendencias s ON i.superintendencia_id = s.id
    INNER JOIN igrejas_enderecos ie ON i.id = ie.igreja_id
    INNER JOIN enderecos en ON ie.endereco_id = en.id
    INNER JOIN estados e ON en.estado_id = e.id
    INNER JOIN cidades c ON en.cidade_id = c.id
    INNER JOIN funcoes_eclesiasticas f ON am.funcao_eclesiastica_id = f.id
WHERE am.ativo = 1
    AND s.id NOT IN (16, 18, 19, 20, 21)
    AND p.data_de_nascimento IS NOT NULL
ORDER BY i.id,
    f.id,
    p.nome;
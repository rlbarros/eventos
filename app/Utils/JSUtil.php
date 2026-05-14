<?php

namespace App\Utils;

class JSUtil
{
    public static function retrieveFromLocalStorageAndDispatch(string $localStorageKey, string $livewireEvent): string
    {
        $js = "(function() {
            let arrayConcatenado = [];

            for (let i = 0; i < localStorage.length; i++) {
                let key = localStorage.key(i);
                
                if (key && key.startsWith('{$localStorageKey}')) {
                    try {
                        // Recupera a string do localStorage
                        let dadoRaw = localStorage.getItem(key);
                        // Converte de volta para Array JavaScript
                        let dadosArray = JSON.parse(dadoRaw); 
                        
                        if (Array.isArray(dadosArray)) {
                            // Adiciona os itens ao array principal (mesma coisa que usar .concat)
                            arrayConcatenado.push(...dadosArray);
                        }
                    } catch (e) {
                        console.error('Erro ao ler a chave do localStorage: ' + key, e);
                    }
                }
            }

            // Envia o array único concatenado de volta para o PHP do Livewire
            console.log(arrayConcatenado);
            Livewire.dispatch('{$livewireEvent}', { participants: arrayConcatenado});
        })();";
        return $js;
    }
}

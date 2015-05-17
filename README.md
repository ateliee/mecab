# meCab reading php library
オープンソース 形態素解析エンジンmeCab(http://taku910.github.io/mecab/)を

phpで利用する場合、インストールが大変なのでライブラリ化してみました。

## 使い方
meCabは別途インストールしている必要があります。

https://github.com/neologd/mecab-ipadic-neologd

```
    "require": {
        "ateliee/mekab": "dev-master"
    }
```

```
use meCab\meCab;
...

$mecab = new meCab();
var_dump($mecab->analysis('すもももももももものうち'));

```
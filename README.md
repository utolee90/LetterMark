# LetterMark
LetterMark는 [나무위키](https://namu.wiki)에서 사용하는 [나무마크](https://namu.wiki/w/%EB%82%98%EB%AC%B4%EC%9C%84%ED%82%A4:%ED%8E%B8%EC%A7%91%20%EB%8F%84%EC%9B%80%EB%A7%90)를 미디어위키 확장기능으로 구현한 코드를 수정한 것입니다. 오리위키 운영자 김동동이 만든 [나무마크 미디어위키판](https://github.com/Oriwiki/php-namumark-mediawiki/archive/master.zip)을 참조해서 만들었습니다.

[php-namumark 라이브러리](https://github.com/koreapyj/php-namumark)를 바탕으로 구성되어 있습니다.

## 라이선스
본 확장기능은 GNU Affero GPL 3.0에 따라 자유롭게 사용하실 수 있습니다. 라이선스에 대한 자세한 사항은 첨부 문서를 참고하십시오.

## 의존
- [Cite 확장기능](https://www.mediawiki.org/wiki/Extension:Cite)
- [Math 확장기능](https://www.mediawiki.org/wiki/Extension:Math) 또는 [SimpleMathJax 확장기능](https://www.mediawiki.org/wiki/Extension:SimpleMathJax)
- [Poem 확장기능](https://www.mediawiki.org/wiki/Extension:Poem)
- [SyntaxHighlight 확장기능](https://www.mediawiki.org/wiki/Extension:SyntaxHighlight) 혹은 [SyntaxHighlighter 확장기능](https://www.mediawiki.org/wiki/Extension:SyntaxHighlighter) (<nowiki>$parser->setHook( 'source', array( $parser->extSyntaxHighlighter, 'source' ) );</nowiki> 아래에 <nowiki>$parser->setHook( 'source', array( $parser->extSyntaxHighlighter, 'syntaxhighlight' ) );</nowiki>를 입력하셔야 합니다. )  
 
## 사용 방법
1. 미디어위키 extensions 폴더에 LetterMark 폴더를 새로 생성합니다. 또는 서버에 직접 git을 이용하실 수 있으면 설치된 미디어위키의 extensions 폴더에서 다음과 같이 명령합니다.

		git clone https://github.com/utolee90/LetterMark.git LetterMark

1. [여기](https://github.com/utolee90/LetterMark/archive/master.zip)를 눌러 다운받은 다음 압축을 풀고, 압축이 풀린 파일을 모두 NamuMark 폴더에 넣습니다. (git으로 한 경우 필요 없습니다.)
1. LocalSettings.php에 다음을 입력합니다.

    ```php
    require_once "$IP/extensions/LetterMark/lettermark.php";
    ```

	
## 그 외
이 코드는 나무마크의 기능 중 몇몇 비권장문법, 외부 이미지 혹은 동영상을 불러들일 수 있는 기능을 제거하였습니다. 원본에서 추가한 기능은 없으니 제거할 이유가 없다면 [원본](https://github.com/Oriwiki/php-namumark-mediawiki/archive/master.zip)을 참조하세요.

자세한 사항에 대해서는 [오리위키의 설명 페이지](http://oriwiki.net/%EB%8F%84%EC%9B%80%EB%A7%90:%EC%9C%84%ED%82%A4_%EB%AC%B8%EB%B2%95/%EB%82%98%EB%AC%B4%EB%A7%88%ED%81%AC) 또는 [큰숲백과의 레터마크 설명 페이지](https://bigforest.miraheze.org/wiki/큰숲백과:LetterMark/)를 참고해주시길 바랍니다.

아직까지 라이브러리의 기능이 완벽하게 구현돼있지 않습니다. 이점을 참고하시고 실제 미디어위키 사이트에 적용하실 때에는 반드시 사전에 시험해보실 것을 권장하는 바입니다.

나무위키에서 호환되지 않는 일부 코드 (각주의 소괄호 표시, 수식 표시시 $$ 기호, font size에서 {{{s 태그 등)이 작동하며, 중괄호 3개만으로는 nowiki 효과가 나지 않고 반드시 {{{n, 또는 {{{x 형태로 입력하셔야 합니다.

## 개선점 

- 2017/2/28 - 일부 [한글] 형식 태그 재지원, {{{f 로 글씨체 변경 기능 추가. "<<" 태그 효과 변경 - "한글:" 입력시에는 모니위키식 파일 파서를 하게 변경. 《 입력 지원. (( 태그는 공백이 없을 경우 작동하지 않게. 
- 2017/3/1 - <nowiki>~~</nowiki>사용시 뒤에 공백이 있어도 활성화. 멀티라인에 <nowiki>/*</nowiki>로 주석처리할 수 있음. 
- 2017/3/8 - <nowiki>**</nowiki> 태그 활성화. <nowiki>(( ))</nowiki> 문법 변경 - 이제 공백 대신 파이프라인으로 각주 태그와 각주 내용을 구별할 수 있음. 
-- <nowiki>{{{f</nowiki>로 글자 변경 제거, 대신 <nowiki>{{{!#font (글자명); (내용)}}}</nowiki>로 글자체 변경 가능. <nowiki>{{{!#align (정렬) (내용)}}}</nowiki>를 이용해서 글자 좌우정렬 기능 추가, <nowiki>{{{!#math (수식)}}}</nowiki>로 수식 추가 가능.
- 2017/4/1 - <nowiki><no</nowiki><nowiki>wiki></nowiki>, <nowiki><pr</nowiki><nowiki>e></nowiki> 태그 안에 있는 내용이 사용자가 입력한 원본 대신 LetterMark에 의해 파싱된 내용으로 출력되는 현상 수정.

pre  태그 비출력문제 해결   
## 버그
- 여러줄에 걸쳐서 <nowiki>{{{</nowiki> 태그를 여러 줄에 걸쳐서 사용할 때 내용물이 숨으면서 '{'만 보이는 현상.
- <nowiki>{{{#!wiki}}}</nowiki> 태그를 한줄에 사용할 때에 제대로 출력하지 못하는 현상.
- pre 태그 내부에서 첫 줄 뒤의 공백을 제대로 인지하지 못하는 현상 
- SyntaxHighlighter 확장기능 사용시에 한 줄만 띄울 경우 br이 syntax 사이에 입력되는 현상.

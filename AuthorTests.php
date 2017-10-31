<?php
//
// Program: AuthorTests.php (2017-10-25) G.J. Watson
//
// Purpose: Author Object Unit Tests
//
// Date       Version Note
// ========== ======= ====================================================
// 2017-10-26 v0.01   Belt and braces test for obj
//

set_include_path("./");
require "Author.php";

$quote1 = new Quote('{"quote_id": 100,"quote_text": "Test Quote1 should be seen here. (used = 99)","times_used": 99}');
print("\n\nConstructor 1");
print("\nQuote ID: ".$quote1->quote_id);
print("\n    Text: ".$quote1->quote_text);
print("\n    Used: ".$quote1->times_used);

$quote2 = new Quote(1, "Quote 2 here");
print("\n\nConstructor 2");
print("\nQuote ID: ".$quote2->quote_id);
print("\n    Text: ".$quote2->quote_text);
print("\n    Used: ".$quote2->times_used);

$quote3 = new Quote();
print("\n\nConstructor 3");
print("\nQuote ID: ".$quote3->quote_id);
print("\n    Text: ".$quote3->quote_text);
print("\n    Used: ".$quote3->times_used);

exit(0);
?>

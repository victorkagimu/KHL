<?php

executeSQL("UPDATE locstock SET quantity=(SELECT SUM(qty) FROM stockmoves WHERE stockmoves.loccode=locstock.loccode AND stockmoves.stockid=locstock.stockid);", $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>
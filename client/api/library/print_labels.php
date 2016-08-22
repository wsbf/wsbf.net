<?php

/**
 * @file library/print_labels.php
 * @author Ben Shealy
 *
 * Render album labels in a PDF document with FPDF.
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("fpdf.php");

define('TOP_X', 0.5);
define('TOP_Y', 1.2);
define('LENGTH_X', 3.5);
define('LENGTH_Y', 5.0);
define('SPACE_X', 0.5);
define('SPACE_Y', 0.0);
define('MARGIN_X', 0.10);
define('MARGIN_Y', 0.18);

/**
 * Get a list of albums from a list of album codes.
 *
 * TODO: try to merge with get_album() from music library functions.
 *
 * @param mysqli     MySQL connection
 * @param album_IDs  array of album IDs
 * @return array of albums
 */
function get_albums($mysqli, $albumIDs)
{
	$keys = array(
		"al.albumID",
		"al.album_code",
		"al.album_name",
		"ar.artist_name",
		"g.genre AS general_genre",
		"al.genre",
		"r.review",
		"u.preferred_name AS reviewer"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libalbum` AS al "
		. "INNER JOIN `libartist` AS ar ON ar.artistID=al.artistID "
		. "INNER JOIN `def_general_genres` AS g ON g.general_genreID=al.general_genreID "
		. "INNER JOIN `libreview` AS r ON r.albumID=al.albumID "
		. "INNER JOIN `users` AS u ON u.username=r.username "
		. "WHERE al.albumID IN ('" . implode("', '", $albumIDs) . "')";
	$result = $mysqli->query($q);

	$albums = array();
	while ( ($a = $result->fetch_assoc()) ) {
		$q = "SELECT disc_num, track_num, track_name, airabilityID FROM `libtrack` "
			. "WHERE albumID = '$a[albumID]';";
		$result_tracks = $mysqli->query($q);

		$a["tracks"] = array();
		while ( ($t = $result_tracks->fetch_assoc()) ) {
			$a["tracks"][] = $t;
		}

		$albums[] = $a;
	}

	return $albums;
}

/**
 * Render an album label in a PDF document.
 *
 * @param pdf      PDF object
 * @param section  section of page
 * @param album    associative array of album
 */
function render_label($pdf, $section, $album)
{
	if ( $section == 0 ) {
		$x = TOP_X;
		$y = TOP_Y;
	}
	else if ( $section == 1 ) {
		$x = TOP_X + LENGTH_X + SPACE_X;
		$y = TOP_Y;
	}
	else if ( $section == 2 ) {
		$x = TOP_X;
		$y = TOP_Y + LENGTH_Y + SPACE_Y;
	}
	else if ( $section == 3 ) {
		$x = TOP_X + LENGTH_X + SPACE_X;
		$y = TOP_Y + LENGTH_Y + SPACE_Y;
	}

	$width = 3.3;
	$height = 4.8;
	$linespace = 0.152;
	$ladywidth = 0.55;
	$ladyoffset = 0.002;
	$alnowidth = 0.45;     // width for the album number
	$aralgenwidth = 0.49;  // width of the area with artist, album, and genre
	$noairwidth = 0.83;
	$rotspacer = 4.3;      // space in the boxes with N, H, M, and L

	$pdf->SetXY($x, $y);
	$pdf->SetFont("", "", 9);
	$pdf->Rect($x - MARGIN_X, $y - MARGIN_Y, LENGTH_X, LENGTH_Y);

	$pdf->Image(
		"$_SERVER[DOCUMENT_ROOT]/images/wsbflady_100.jpg",
		$x, $y,
		$ladywidth - $ladyoffset
	);

	// general genre
	$pdf->SetX($x + $ladywidth);
	$pdf->Cell(
		$width - $ladywidth - $alnowidth - ($rotspacer * $linespace),
		$linespace,
		$album["general_genre"], 1, 0, "C"
	);

	// rotations
	$pdf->SetX($x + ($width - $alnowidth) - ($rotspacer * $linespace));
	$pdf->Cell($linespace, $linespace, "N", "LTRB", 0, "C");
	$pdf->Cell($linespace, $linespace, "H", "LTRB", 0, "C");
	$pdf->Cell($linespace, $linespace, "M", "LTRB", 0, "C");
	$pdf->Cell($linespace, $linespace, "L", "LTRB", 0, "C");

	// album code
	$pdf->SetX($x + ($width - $alnowidth) + 0.08);
	$pdf->SetFont("", "B", 16);
	$pdf->Cell(
		$alnowidth,
		$linespace,
		$album["album_code"], 0, 1, "R"
	);

	// artist name
	$pdf->SetX($x + $ladywidth);
	$pdf->SetFont("", "B", 9);
	$pdf->Cell($aralgenwidth, $linespace, "Artist: ");

	$pdf->SetFont("", "");
	$pdf->MultiCell(
		$width - $ladywidth - $aralgenwidth,
		$linespace,
		$album["artist_name"]
	);

	// album name
	$pdf->SetX($x + $ladywidth);
	$pdf->SetFont("", "B");
	$pdf->Cell($aralgenwidth, $linespace, "Album: ");

	$pdf->SetFont("", "");
	$pdf->MultiCell(
		$width - $ladywidth - $aralgenwidth,
		$linespace,
		$album["album_name"]
	);

	// genre
	$pdf->SetX($x + $ladywidth);
	$pdf->SetFont("", "B");
	$pdf->Cell($aralgenwidth, $linespace, "Genre: ");

	$pdf->SetFont("", "");
	$pdf->MultiCell(
		$width - $ladywidth - $aralgenwidth,
		$linespace,
		$album["genre"]
	);

	// review
	$pdf->SetX($x);
	$pdf->SetFont("", "B", 9);
	$pdf->Cell($width, $linespace, "Property of WSBF-FM Clemson - 88.1", "", 1, "C");

	$pdf->SetX($x);
	$pdf->SetFont("", "", 9);
//	$review = convert_smart_quotes($review);
	$pdf->MultiCell(
		$width,
		$linespace,
		$album["review"], "LTR", "L"
	);

	// reviewer
	$pdf->SetX($x);
	$pdf->SetFont("", "B", "");
	$pdf->Cell(0.8, $linespace, "Reviewed by:", "L", "L");

	$pdf->SetFont("", "", 9);
	$pdf->Cell(
		$width - 0.8,
		$linespace,
		$album["reviewer"], "RB", 1, "L"
	);

	// recommended tracks
	$recs = array_filter($album["tracks"], function($t) {
		return $t["airabilityID"] == 1;
	});
	$recs = array_map(function($t) {
		if ( $t["disc_num"] == 1 ) {
			return "$t[track_num].$t[track_name]";
		}
		else {
			return "D$t[disc_num].T$t[track_num].$t[track_name]";
		}
	}, $recs);

	if ( count($recs) > 0 ) {
		$pdf->SetX($x);
		$pdf->SetFont('','B','');
		$pdf->Cell($width, $linespace, "Recommended:", "TR", "L", 2);

		$pdf->SetX($x);
		$pdf->SetFont('','',9);
		$pdf->MultiCell(
			$width,
			$linespace,
			"             " . implode(", ", $recs),
			"LR", "L"
		);
	}
	else {
		$pdf->SetX($x);
		$pdf->SetFont('','B');
		$pdf->Cell(
			$width,
			$linespace,
			"Album Apparently Has No Recommended Tracks?",
			"T", 1, "C"
		);
	}

	// no-air tracks
	$noairs = array_filter($album["tracks"], function($t) {
		return $t["airabilityID"] == 2;
	});
	$noairs = array_map(function($t) {
		if ( $t["disc_num"] == 1 ) {
			return "$t[track_num]";
		}
		else {
			return "D$t[disc_num].T$t[track_num]";
		}
	}, $noairs);

	if ( count($noairs) > 0 ) {
		$pdf->SetX($x);
		$pdf->SetFont('','B',9);
		$pdf->Cell($width, $linespace, "No-Air:", "TR", "L", 2);

		$pdf->SetX($x);
		$pdf->SetFont('','',9);
		$pdf->MultiCell(
			$width,
			$linespace,
			"        " . implode(", ", $noairs),
			"LRTB", "L"
		);
	} else {
		$pdf->SetX($x);
		$pdf->SetFont('','B');
		$pdf->Cell($width, $linespace, "Album Is FCC Clean", "T", 1, "C");
	}

	// silence-after tracks
	$silences = array_filter($album["tracks"], function($t) {
		return $t["airabilityID"] == 3;
	});
	$silences = array_map(function($t) {
		if ( $t["disc_num"] == 1 ) {
			return "$t[track_num]";
		}
		else {
			return "D$t[disc_num].T$t[track_num]";
		}
	}, $silences);

	if ( count($silences) > 0 ) {
		$pdf->SetX($x);
		$pdf->SetFont('','B',9);
		$pdf->Cell($width, $linespace, "Note:", "TR", "L", 2);

		$pdf->SetX($x);
		$pdf->SetFont('','',9);
		$pdf->MultiCell(
			$width,
			$linespace,
			"      " . implode(", ", $silences) . " has silence after track.",
			"LRTB", "L"
		);
	}
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to print album labels.");
	}

	// validate input
	$albumIDs = $_GET["albums"];
	$albumIDs = escape_json($mysqli, $albumIDs);

	// initialize PDF
	$pdf = new FPDF("P", "in", "A4");
	$pdf->AddFont("Basicmanual", "", "svbasicmanual.php");
	$pdf->AddFont("Basicmanual", "B", "svbasicmanual-bold.php");
	$pdf->SetFont("Basicmanual", "", 12);

	// render labels to PDF
	$albums = get_albums($mysqli, $albumIDs);
	$section = 0;

	foreach ( $albums as $album ) {
		if ( $section == 0 ) {
			$pdf->AddPage();
		}

		render_label($pdf, $section, $album);

		$section = ($section + 1) % 4;
	}

	// return PDF document
	$pdf->Output("CDLabel.pdf", "D");
}
?>

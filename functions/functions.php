<?php
/**
 * 他の年の記事があるかどうか
 *
 * @param string $school_name 学校名.
 *
 * @return bool
 */
function is_other_year_post( $school_name ) {
	$result = new WP_Query( array( 'meta_value' => $school_name ) );
	$number = $result->found_posts;
	if ( 1 === $number ) :
		return false;
	else :
		return true;
	endif;
}

/**
 * 文化祭が終了しているか
 */
function is_ended() {
	if ( ! get_field( 'endDate' ) ) {
		$end_date = get_field( 'startDate' );
	} else {
		$end_date = get_field( 'endDate' );
	}
	if ( $end_date < date( 'Y/m/d' ) ) :
		return true;
	else :
		return false;
	endif;
}

/**
 * 文化祭までの残り日数を取得
 */
function get_remaining_days() {
	if ( get_field( 'startDate' ) and get_field( 'endDate' ) ) {
		$start_date = get_field( 'startDate' );
		$today      = date( 'Y/m/d' );
		$days       = abs( strtotime( $start_date ) - strtotime( $today ) ) / ( 60 * 60 * 24 );

		return $days;
	} else {
		return null;
	}
}

/**
 * 終了日を取得
 */
function get_end_date() {
	if ( ! get_field( 'endDate' ) ) {
		$end_date = get_field( 'startDate' );
	} else {
		$end_date = get_field( 'endDate' );
	}

	return $end_date;
}

/**
 * 文化祭が開催中かどうか
 * 開催中:true
 * 開催前:null
 * 終了:false
 */
function is_bunkasai_during_open() {
	if ( get_field( 'startDate' ) ) {
		$start_date = get_field( 'startDate' );
		$today      = date( 'Y/m/d' );
		if ( ! get_field( 'endDate' ) ) {
			// 開催期間が1日の文化祭を想定する.
			$end_date = get_field( 'startDate' );
		} else {
			$end_date = get_field( 'endDate' );
		}
		if ( $today === $start_date ) {
			// 開始日当日.
			return true;
		} elseif ( $today === $end_date ) {
			// 終了日当日.
			return true;
		} elseif ( $today > $end_date ) {
			// 終了日よりも後.
			return false;
		} elseif ( $today > $start_date and $today < $end_date ) {
			// 開催期間中.
			// 開始日よりも後 終了日よりも前.
			return true;
		} elseif ( $today < $start_date ) {
			// 開始日よりも前 残り日数を計算する.
			return null;
		}
	}
}

/**
 * 全ての記事を取得する
 */
function get_all_posts() {
	$args  = array(
		'meta_query' => array(
			'meta' => array(
				'key'  => 'schoolName',
				'type' => 'CHAR',
			),
		),
		'post_type'  => 'post',
		'order'      => 'ASC',
		'orderby'    => 'meta',
		'posts_per_page' => -1,
	);
	$posts = new WP_Query( $args );
	return $posts;
}

/**
 * 全ての学校の情報を取得する関数
 *
 * 学校名と所在地の入った連想配列を返す。
 *
 * @todo 処理の効率化。
 *
 * @return array
 */
function get_all_school_information() {
	$posts                  = get_all_posts();
	$all_school_information = [];
	$loop_tmp               = [];
	if ( $posts->have_posts() ) :
		while ( $posts->have_posts() ) :
			$posts->the_post();
			$school_name = get_field( 'schoolName' );
			if ( ! in_array( $school_name, $loop_tmp, true ) ) :
				$category                 = get_the_category();
				$all_school_information[] = [
					'school_name'       => $school_name,
					'municipality_name' => $category[0]->cat_name,
				];
				$loop_tmp[]               = $school_name;
			endif;
		endwhile;
		// 列方向の配列を得る.
		foreach ( $all_school_information as $key => $value ) {
			$municipality_name[ $key ] = $value['municipality_name'];
		}
		// データを $municipality_name の昇順にソートする.
		// $all_school_information を最後のパラメータとして渡し、同じキーでソートする.
		array_multisort( $municipality_name, SORT_ASC, SORT_STRING, $all_school_information );
	endif;

	return $all_school_information;
}

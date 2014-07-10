/*
* mysql 自定义函数
*
*/

set global log_bin_trust_function_creators=1;


/*
* 计算poi点距离
*
*/
DELIMITER $$
CREATE DEFINER = CURRENT_USER FUNCTION `getDistance`(`lon1` float,`lat1` float,`lon2` float,`lat2` float)
 RETURNS double
begin
    declare d double;
    declare radius int;
    set radius = 6378140; #假设地球为正球形，直径为6378140米
    set d = (2*ATAN2(SQRT(SIN((lat1-lat2)*PI()/180/2)  
        *SIN((lat1-lat2)*PI()/180/2)+  
        COS(lat2*PI()/180)*COS(lat1*PI()/180)  
        *SIN((lon1-lon2)*PI()/180/2)  
        *SIN((lon1-lon2)*PI()/180/2)),  
        SQRT(1-SIN((lat1-lat2)*PI()/180/2)  
        *SIN((lat1-lat2)*PI()/180/2)  
        +COS(lat2*PI()/180)*COS(lat1*PI()/180)  
        *SIN((lon1-lon2)*PI()/180/2)  
        *SIN((lon1-lon2)*PI()/180/2))))*radius;
    return d;
END $$
DELIMITER $$;

/*
* 字符串分割
*
*/
DELIMITER $$
CREATE DEFINER = CURRENT_USER FUNCTION `str_split`(`s` varchar(100),`p` varchar(100),`id` int)
 RETURNS varchar(100) CHARSET utf8
BEGIN
	declare result varchar(255) default '';
	set result = reverse(substring_index(reverse(substring_index(s,p,id)),p,1)); 
	RETURN result;
END $$
DELIMITER $$;


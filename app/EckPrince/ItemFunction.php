<?php
/**
 * Created by PhpStorm.
 * User: TuanLe
 * Date: 10/10/2017
 * Time: 5:48 PM
 */

namespace App\EckPrince;


class ItemFunction
{
    /*
     * Đọc nội dung file Item_Data và trả về 1 mảng các item trong file đó
     */
    function ItemDataArr($file_data = "Item_Data.txt")
    {
        $fopen_host = fopen($file_data, "r");
        $item_data = fgets($fopen_host);
        fclose($fopen_host);

        $item_data_arr = json_decode($item_data, true);

        foreach ($item_data_arr as $item_v) {
            $group = $item_v[1];
            $id = $item_v[2];
            if (strlen($item_v[8]) > 0) {
                $item_name = $item_v[8];
                $item_name_en = $item_v[3];
            } else {
                $item_name = $item_v[3];
                $item_name_en = $item_v[3];
            }

            $item_read[$group][$id][] = array(
                'image' => $item_v[0],
                'group' => $item_v[1],
                'id' => $item_v[2],
                'name' => $item_name,
                'name_en' => $item_name_en,
                'x' => $item_v[4],
                'y' => $item_v[5],
                'set1' => $item_v[6],
                'set2' => $item_v[7],
                'groupid' => $item_v[9],
                'dw' => $item_v[10],
                'dk' => $item_v[11],
                'elf' => $item_v[12],
                'mg' => $item_v[13],
                'dl' => $item_v[14],
                'sum' => $item_v[15],
                'rf' => $item_v[16]
            );
        }

        return $item_read;

        /*    0	    Sword
              1	    Rìu
              2	    Truỳ & trượng
              3 	Thương Giáo
              4	    Giáo
              5	    Cung
              6 	Nỏ
              7	    Gậy
              8	    Shield
              9	    Mũ
              10 	Áo
              11	Quần
              14 	Cánh
              15	Pet
              16	Ring
              17	Pent
              18	Ngọc Skill
              19	Sách phép
              20    Jewel
              21	Sách phép bùa chú
              22	Seal
              23	Vé Event
              24	Nguyên liệu ép vé
              25	Quest
              26	Hộp Quà, nhẫn phù thuỷ
              27	NL ép sói
              28	Ngọc nguyên tố, Khuôn, Lông vũ
              29	....
              30	Bùa, thư
              31	Ngọc tỉ lệ, bùa chaos*/
    }

    /*
     * Lấy ra 1 item theo Item_ID và Group trong mảng Item
     */
    function ItemsData($itemdata_arr, $id, $group, $item_level = 0)
    {
        $item_level_special_group13_arr = array(16, 17, 18, 49, 50, 51);
        $item_level_special_group14_arr = array(11, 17, 18, 19, 28, 29);
        $item_level_special_arr = array(1, 16, 24);
        if (($group == 13 && in_array($id, $item_level_special_group13_arr)) || ($group == 14 && in_array($id, $item_level_special_group14_arr) && !in_array($item_level, $item_level_special_arr))) {
            $item_level = $item_level - 1;
            if ($item_level < 0) $item_level = 0;
        }


        if (!isset($itemdata_arr[$group][$id][$item_level])) {
            return $itemdata_arr[$group][$id][0];
        } else return $itemdata_arr[$group][$id][$item_level];
    }

    function GetCode($string)
    {
        // Phân tich Mã Item 32 số
        $id = hexdec(substr($string, 0, 2));    // Item ID
        $group = hexdec(substr($string, 18, 1));    // Item Type
        $option = hexdec(substr($string, 2, 2));    // Item Level/Skill/Option Data
        $level = floor($option / 8);
        $Num = $group * 512 + $id;
        $durability = hexdec(substr($string, 4, 2));    // Item Durability
        $dur = $durability;
        $serial = substr($string, 6, 8);        // Item SKey
        $exc_option = hexdec(substr($string, 14, 2));    // Item Excellent Info/ Option
        $ancient = hexdec(substr($string, 16, 2));    // Ancient data
        $harmony = hexdec(substr($string, 20, 2));
        $socket_slot[1] = hexdec(substr($string, 22, 2));    // Socket data
        $socket_slot[2] = hexdec(substr($string, 24, 2));    // Socket data
        $socket_slot[3] = hexdec(substr($string, 26, 2));    // Socket data
        $socket_slot[4] = hexdec(substr($string, 28, 2));    // Socket data
        $socket_slot[5] = hexdec(substr($string, 30, 2));    // Socket data

        // Kiểm tra Item có tuyệt chiêu
        if ($option < 128) $skill = 0; else $skill = 1;
        // Kiểm tra Item luck
        if ($option < 4) $luck = 0; else $luck = 1;

        $output = array(
            'id' => $id,
            'group' => $group,
            'Num' => $Num,
            'Lvl' => $level,
            'Opt' => $option,
            'Luck' => $luck,
            'Skill' => $skill,
            'Dur' => $dur,
            'Excellent' => $exc_option,
            'Ancient' => $ancient,
            'JOH' => $harmony,
            'Sock1' => $socket_slot[1],
            'Sock2' => $socket_slot[2],
            'Sock3' => $socket_slot[3],
            'Sock4' => $socket_slot[4],
            'Sock5' => $socket_slot[5]
        );
        return $output;
    }

    function ItemImage($id, $group, $ExclAnci, $level = 0)
    {
        switch ($ExclAnci) {
            case 1:
                $tnpl = '10';
                break;
            case 2:
                $tnpl = '01';
                break;
            default:
                $tnpl = '00';
        }

        $item_type = $group * 16;
        if ($id > 31) {
            $nxt = 'F9';
            $id += -32;
        } else $nxt = '00';
        if ($item_type < 128) {
            $tipaj = '00';
            $id += $item_type;
        } else {
            $tipaj = '80';
            $item_type += -128;
            $id += $item_type;
        }
        $item_type += $id;
        $item_type = sprintf("%02X", $item_type, 00);
        if (file_exists('items/' . $tnpl . $item_type . $tipaj . $nxt . '.gif')) $output = 'items/' . $tnpl . $item_type . $tipaj . $nxt . '.gif';
        else $output = 'items/00' . $item_type . $tipaj . $nxt . '.gif';
        $i = $level + 1;
        while ($i > 0) {
            $i += -1;
            $item_level = sprintf("%02X", $i, 00);
            if (file_exists('items/' . $tnpl . $item_type . $tipaj . $nxt . $item_level . '.gif')) {
                $output = 'items/' . $tnpl . $item_type . $tipaj . $nxt . $item_level . '.gif';
                $i = 0;
            }
        }
        if (!file_exists($output)) $output = 'items/SinFoto.gif';
        return $output;
    }

    function ItemInfo($itemdata_arr, $string)
    {
        if ($string == 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF' || $string == 'ffffffffffffffffffffffffffffffff') {
            $output = '';
            return $output;
        }
        $item_code = $string;
        // Phân tich Mã Item 32 số
        $id = hexdec(substr($string, 0, 2));    // Item ID
        $group = hexdec(substr($string, 18, 1));    // Item Type
        $option = hexdec(substr($string, 2, 2));    // Item Level/Skill/Option Data
        $durability = hexdec(substr($string, 4, 2));    // Item Durability
        $dur = $durability;
        $is_limited = substr($string, 19, 1);
        $serial = substr($string, 6, 8);        // Item SKey
        $exc_option = hexdec(substr($string, 14, 2));    // Item Excellent Info/ Option
        $ancient = hexdec(substr($string, 16, 2));    // Ancient data
        $harmony = hexdec(substr($string, 20, 2));
        $socket_slot[1] = hexdec(substr($string, 22, 2));    // Socket data
        $socket_slot[2] = hexdec(substr($string, 24, 2));    // Socket data
        $socket_slot[3] = hexdec(substr($string, 26, 2));    // Socket data
        $socket_slot[4] = hexdec(substr($string, 28, 2));    // Socket data
        $socket_slot[5] = hexdec(substr($string, 30, 2));    // Socket data
        // Điều chỉnh Item Thần
        if ($ancient == 4) $ancient = 5;
        if ($ancient == 9) $ancient = 10;
        // Kiểm tra Item có tuyệt chiêu
        if ($option < 128) $skill = '';
        else {
            $skill = '<font color=#8CB0EA>Vũ khí có tuyệt chiêu</font><br>';
            $option = $option - 128;
        }
        // Kiểm tra Cấp độ Item
        $item__level = floor($option / 8);
        $item_level = $item__level;
        $option = $option - $item_level * 8;
        // Kiểm tra Item luck
        if ($option < 4) $luck = '';
        else {
            $luck = '<font color=#8CB0EA>May Mắn (Tỉ lệ ép Ngọc Tâm Linh + 25%)<br>May Mắn (Sát thương tối đa + 5%)</font><br>';
            $option = $option - 4;
        }
        // Kiểm tra Excellent Option
        $exc_total = 0;
        $exc_opt = $exc_option;
        if ($exc_option >= 64) {
            $option += 4;
            $exc_option += -64;
        }

        if ($exc_option < 32) {
            $iopx6 = 0;
        } else {
            $iopx6 = 1;
            $exc_option += -32;
            $exc_total++;
        }

        if ($exc_option < 16) {
            $iopx5 = 0;
        } else {
            $iopx5 = 1;
            $exc_option += -16;
            $exc_total++;
        }

        if ($exc_option < 8) {
            $iopx4 = 0;
        } else {
            $iopx4 = 1;
            $exc_option += -8;
            $exc_total++;
        }

        if ($exc_option < 4) {
            $iopx3 = 0;
        } else {
            $iopx3 = 1;
            $exc_option += -4;
            $exc_total++;
        }

        if ($exc_option < 2) {
            $iopx2 = 0;
        } else {
            $iopx2 = 1;
            $exc_option += -2;
            $exc_total++;
        }

        if ($exc_option < 1) {
            $iopx1 = 0;
        } else {
            $iopx1 = 1;
            $exc_option += -1;
            $exc_total++;
        }

        $item_type = 99;
        if ($group < 6) $item_type = 0;
        else if ($group == 6) $item_type = 1;
        else if ($group < 12) $item_type = 2;
        else if ($group == 12 && in_array($id, array(36, 37, 38, 39, 40, 43, 50))) $item_type = 7;
        else if ($group == 12 && in_array($id, array(240, 241, 242, 243, 244, 245))) $item_type = 9;
        else if ($group == 12 && in_array($id, array(246, 247, 248, 249))) $item_type = 8;
        else if (($group == 12 && in_array($id, array(3, 4, 5, 6, 42, 49))) || ($group == 13 && in_array($id, array(30)))) $item_type = 3;
        else if ($group == 13 && in_array($id, array(8, 9, 21, 22, 23, 24, 39, 40, 41))) $item_type = 4;
        else if ($group == 13 && in_array($id, array(12, 13, 25, 26, 27, 28))) $item_type = 5;
        else if ($group == 13 && $id == 37) $item_type = 6;

        $item_exc = '';
        $nocolor = false;
        switch ($item_type) {
            case 0 :
                $op1 = 'Tăng lượng MANA khi giết quái (MANA/8)';
                $op2 = 'Tăng lượng LIFE khi giết quái (LIFE/8)';
                $op3 = 'Tốc độ tấn công +7';
                $op4 = 'Tăng lực tấn công 2%';
                $op5 = 'Tăng lực tấn công (Cấp độ/20)';
                $op6 = 'Khả năng xuất hiện lực tấn công hoàn hảo +10%';
                $option_type = 'Tăng thêm sát thương';
                $option_bonus = $option * 4;
                break;
            case 1:
                $op1 = 'Lượng ZEN rơi ra khi giết quái +40%';
                $op2 = 'Khả năng xuất hiện phòng thủ hoàn hảo +10%';
                $op3 = 'Phản hồi sát thương +5%';
                $op4 = 'Giảm sát thương +4%';
                $op5 = 'Lượng MANA tối đa +4%';
                $op6 = 'Lượng HP tối đa +4%';
                $option_type = 'Tăng thêm phòng thủ';
                $option_bonus = $option * 5;
                break;
            case 2:
                $op1 = 'Lượng ZEN rơi ra khi giết quái +40%';
                $op2 = 'Khả năng xuất hiện phòng thủ hoàn hảo +10%';
                $op3 = 'Phản hồi sát thương +5%';
                $op4 = 'Giảm sát thương +4%';
                $op5 = 'Lượng MANA tối đa +4%';
                $op6 = 'Lượng HP tối đa +4%';
                $option_type = 'Tăng thêm phòng thủ';
                $option_bonus = $option * 4;
                $skill = '';
                break;
            case 3:
                $op1 = '+ 115 Lượng HP tối đa';
                $op2 = '+ 115 Lượng MP tối đa';
                $op3 = 'Khả năng loại bỏ phòng thủ đối phương +3%';
                $op4 = '+ 50 Lực hành động tối đa';
                $op5 = 'Tốc độ tấn công +7';
                $op6 = '';
                $option_type = 'Tự động hồi phục HP';
                $option_bonus = $option . '%';
                $skill = '';
                $nocolor = true;
                if ($iopx6 == 1) --$exc_total;
                break;
            case 4:
                $op1 = 'Lượng ZEN rơi ra khi giết quái +40%';
                $op2 = 'Khả năng xuất hiện phòng thủ hoàn hảo +10%';
                $op3 = 'Phản hồi sát thương +5%';
                $op4 = 'Giảm sát thương +4%';
                $op5 = 'Lượng MANA tối đa 4%';
                $op6 = 'Lượng HP tối đa 4%';
                $option_type = 'Tự động hồi phục HP';
                $option_bonus = $option . '%';
                $skill = '';
                break;
            case 5:
                $op1 = 'Tăng lượng MANA khi giết quái (MANA/8)';
                $op2 = 'Tăng lượng LIFE khi giết quái (LIFE/8)';
                $op3 = 'Tốc độ tấn công +7';
                $op4 = 'Tăng lực tấn công 2%';
                $op5 = 'Tăng lực tấn công (Cấp độ/20)';
                $op6 = 'Khả năng xuất hiện lực tấn công hoàn hảo +10%';
                $option_type = 'Tự động hồi phục HP';
                $option_bonus = $option . '%';
                $skill = '';
                break;
            case 6:
                $op1 = 'Gia tăng mức phá hủy +10%<br>Tăng tốc độ di chuyển';
                $op2 = 'Gia tăng mức phòng thủ +10%<br>Tăng tốc độ di chuyển';
                $op3 = 'Tăng tốc độ di chuyển';
                $op4 = '';
                $op5 = '';
                $op6 = '';
                $option_type = '';
                $option_bonus = '';
                $skill = 'Tuyệt chiêu Bão Điện (MANA:50)';
                if ($iopx4 == 1) --$exc_total;
                if ($iopx5 == 1) --$exc_total;
                if ($iopx6 == 1) --$exc_total;
                break;
            case 7:
                $op1 = '5% cơ hội loại bỏ sức phòng thủ';
                $op2 = '5 % phản đòn khi cận chiến';
                $op3 = '5% khả năng hồi phục hoàn toàn HP';
                $op4 = '5% khả năng hồi phục hoàn toàn nội lực';
                $op5 = '';
                $op6 = '';
                $option_type = 'Tự động hồi phục HP';
                $option_bonus = $option . '%';
                $skill = '';
                $nocolor = true;
                if ($iopx5 == 1) --$exc_total;
                if ($iopx6 == 1) --$exc_total;
                break;
            case 8:
                $op1 = '3% cơ hội loại bỏ sức phòng thủ';
                $op2 = '3 % phản đòn khi cận chiến';
                $op3 = '3% khả năng hồi phục hoàn toàn HP';
                $op4 = '3% khả năng hồi phục hoàn toàn nội lực';
                $op5 = '';
                $op6 = '';
                $option_type = 'Tự động hồi phục HP';
                $option_bonus = $option . '%';
                $skill = '';
                $nocolor = true;
                if ($iopx5 == 1) --$exc_total;
                if ($iopx6 == 1) --$exc_total;
                break;
            case 9:
                $op1 = '7% cơ hội loại bỏ sức phòng thủ';
                $op2 = '7 % phản đòn khi cận chiến';
                $op3 = '7% khả năng hồi phục hoàn toàn HP';
                $op4 = '7% khả năng hồi phục hoàn toàn nội lực';
                $op5 = '';
                $op6 = '';
                $option_type = 'Tự động hồi phục HP';
                $option_bonus = $option . '%';
                $skill = '';
                $nocolor = true;
                if ($iopx5 == 1) --$exc_total;
                if ($iopx6 == 1) --$exc_total;
                break;
            default:
                $op1 = '';
                $op2 = '';
                $op3 = '';
                $op4 = '';
                $op5 = '';
                $op6 = '';
                $option_type = '';
                $option_bonus = '';
                $skill = '';
                $nocolor = true;
        }
        if ($option_bonus != 0) $item_option = '<font color=#9AADD5>' . $option_type . ' +' . $option_bonus . '</font><br>'; else $item_option = '';
        if ($iopx1 == 1) $item_exc .= '<br>' . $op1;
        if ($iopx2 == 1) $item_exc .= '<br>' . $op2;
        if ($iopx3 == 1) $item_exc .= '<br>' . $op3;
        if ($iopx4 == 1) $item_exc .= '<br>' . $op4;
        if ($iopx5 == 1) $item_exc .= '<br>' . $op5;
        if ($iopx6 == 1) $item_exc .= '<br>' . $op6;

        //Kiểm tra Socket Item
        $item_socket = '';
        for ($slot = 1; $slot < 6; $slot++) {
            if ($socket_slot[$slot] == 0) $socket[$slot] = 0;
            else if ($socket_slot[$slot] == 255) {
                $socket_type[$slot] = '(Chưa khảm dòng socket)';
                $socket[$slot] = 1;
            } else {
                switch ($socket_slot[$slot]) {
                    case 1:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật (theo Level) + 20';
                        $socket[$slot] = 1;
                        break;
                    case 2:
                        $socket_type[$slot] = 'Lửa (Tăng tốc độ tấn công) + 7';
                        $socket[$slot] = 1;
                        break;
                    case 3:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật tối đa) + 30';
                        $socket[$slot] = 1;
                        break;
                    case 4:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật tối thiểu) + 20';
                        $socket[$slot] = 1;
                        break;
                    case 5:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật) + 20';
                        $socket[$slot] = 1;
                        break;
                    case 6:
                        $socket_type[$slot] = 'Lửa (Giảm lượng AG khi dùng kỹ năng) + 40';
                        $socket[$slot] = 1;
                        break;
                    case 11:
                        $socket_type[$slot] = 'Nước (Tăng tỷ lệ phòng thủ) + 10';
                        $socket[$slot] = 1;
                        break;
                    case 12:
                        $socket_type[$slot] = 'Nước (Tăng sức phòng thủ) + 30';
                        $socket[$slot] = 1;
                        break;
                    case 13:
                        $socket_type[$slot] = 'Nước (Tăng khả năng phòng vệ của khiên) + 7';
                        $socket[$slot] = 1;
                        break;
                    case 14:
                        $socket_type[$slot] = 'Nước (Giảm sát thương) + 4';
                        $socket[$slot] = 1;
                        break;
                    case 15:
                        $socket_type[$slot] = 'Nước (Phản hồi sát thương) + 5';
                        $socket[$slot] = 1;
                        break;
                    case 17:
                        $socket_type[$slot] = 'Băng (Tăng khả năng hồi phục HP khi giết quái vật) + 8';
                        $socket[$slot] = 1;
                        break;
                    case 18:
                        $socket_type[$slot] = 'Băng (Tăng khả năng hồi phục Mana khi giết quái vật) + 8';
                        $socket[$slot] = 1;
                        break;
                    case 19:
                        $socket_type[$slot] = 'Băng (Tăng sức sát thương kỹ năng) + 37';
                        $socket[$slot] = 1;
                        break;
                    case 20:
                        $socket_type[$slot] = 'Băng (Tăng lực tấn công) + 25';
                        $socket[$slot] = 1;
                        break;
                    case 21:
                        $socket_type[$slot] = 'Băng (Tăng độ bền vật phẩm) + 30';
                        $socket[$slot] = 1;
                        break;
                    case 22:
                        $socket_type[$slot] = 'Gió (Tự động hồi phục HP) + 8';
                        $socket[$slot] = 1;
                        break;
                    case 23:
                        $socket_type[$slot] = 'Gió (Tăng HP tối đa) + 4';
                        $socket[$slot] = 1;
                        break;
                    case 24:
                        $socket_type[$slot] = 'Gió (Tăng Mana tối đa) + 4';
                        $socket[$slot] = 1;
                        break;
                    case 25:
                        $socket_type[$slot] = 'Gió (Tự động hồi phục Mana) + 7';
                        $socket[$slot] = 1;
                        break;
                    case 26:
                        $socket_type[$slot] = 'Gió (Tăng AG tối đa) + 25';
                        $socket[$slot] = 1;
                        break;
                    case 27:
                        $socket_type[$slot] = 'Gió (Tăng lượng AG) + 3';
                        $socket[$slot] = 1;
                        break;
                    case 30:
                        $socket_type[$slot] = 'Sét (Tăng sát thương hoàn hảo) + 15';
                        $socket[$slot] = 1;
                        break;
                    case 31:
                        $socket_type[$slot] = 'Sét (Tăng tỷ lệ sát thương hoàn hảo) + 10';
                        $socket[$slot] = 1;
                        break;
                    case 32:
                        $socket_type[$slot] = 'Sét (Tăng sát thương chí mạng) + 30';
                        $socket[$slot] = 1;
                        break;
                    case 33:
                        $socket_type[$slot] = 'Sét (Tăng tỷ lệ sát thương chí mạng) + 8';
                        $socket[$slot] = 1;
                        break;
                    case 37:
                        $socket_type[$slot] = 'Đất (Tăng thể lực) + 30';
                        $socket[$slot] = 1;
                        break;
                    case 51:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật (theo Level) + 400';
                        $socket[$slot] = 1;
                        break;
                    case 52:
                        $socket_type[$slot] = 'Lửa (Tăng tốc độ đánh) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 53:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 54:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật tối thiểu) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 55:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 56:
                        $socket_type[$slot] = 'Lửa (Giảm lượng AG khi dùng kỹ năng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 61:
                        $socket_type[$slot] = 'Nước (Tăng tỷ lệ phòng thủ) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 62:
                        $socket_type[$slot] = 'Nước (Tăng sức phòng thủ) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 63:
                        $socket_type[$slot] = 'Nước (Tăng khả năng phòng vệ của khiên) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 64:
                        $socket_type[$slot] = 'Nước (Giảm sát thương) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 65:
                        $socket_type[$slot] = 'Nước (Phản hồi sát thương) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 67:
                        $socket_type[$slot] = 'Băng (Tăng khả năng hồi phục HP khi giết quái vật) + 49';
                        $socket[$slot] = 1;
                        break;
                    case 68:
                        $socket_type[$slot] = 'Băng (Tăng khả năng hồi phục Mana khi giết quái vật) + 49';
                        $socket[$slot] = 1;
                        break;
                    case 69:
                        $socket_type[$slot] = 'Băng (Tăng sức sát thương kỹ năng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 70:
                        $socket_type[$slot] = 'Băng (Tăng lực tấn công) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 71:
                        $socket_type[$slot] = 'Băng (Tăng độ bền vật phẩm) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 72:
                        $socket_type[$slot] = 'Gió (Tự động hồi phục HP) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 73:
                        $socket_type[$slot] = 'Gió (Tăng HP tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 74:
                        $socket_type[$slot] = 'Gió (Tăng Mana tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 75:
                        $socket_type[$slot] = 'Gió (Tự động hồi phục Mana) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 76:
                        $socket_type[$slot] = 'Gió (Tăng AG tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 77:
                        $socket_type[$slot] = 'Gió (Tăng lượng AG) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 80:
                        $socket_type[$slot] = 'Sét (Tăng sát thương hoàn hảo) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 81:
                        $socket_type[$slot] = 'Sét (Tăng tỷ lệ sát thương hoàn hảo) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 82:
                        $socket_type[$slot] = 'Sét (Tăng sát thương chí mạng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 83:
                        $socket_type[$slot] = 'Sét (Tăng tỷ lệ sát thương chí mạng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 87:
                        $socket_type[$slot] = 'Đất (Tăng thể lực) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 101:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật (theo Level) + 400';
                        $socket[$slot] = 1;
                        break;
                    case 102:
                        $socket_type[$slot] = 'Lửa (Tăng tốc độ đánh) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 103:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 104:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật tối thiểu) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 105:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 106:
                        $socket_type[$slot] = 'Lửa (Giảm lượng AG khi dùng kỹ năng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 111:
                        $socket_type[$slot] = 'Nước (Tăng tỷ lệ phòng thủ) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 112:
                        $socket_type[$slot] = 'Nước (Tăng sức phòng thủ) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 113:
                        $socket_type[$slot] = 'Nước (Tăng sức phòng thủ Shield) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 114:
                        $socket_type[$slot] = 'Nước (Giảm sát thương) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 115:
                        $socket_type[$slot] = 'Nước (Phản hồi sát thương) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 117:
                        $socket_type[$slot] = 'Băng (Tăng khả năng hồi phục HP khi giết quái vật) + 50';
                        $socket[$slot] = 1;
                        break;
                    case 118:
                        $socket_type[$slot] = 'Băng (Tăng khả năng hồi phục Mana khi giết quái vật) + 50';
                        $socket[$slot] = 1;
                        break;
                    case 119:
                        $socket_type[$slot] = 'Băng (Tăng sức sát thương kỹ năng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 120:
                        $socket_type[$slot] = 'Băng (Tăng lực tấn công) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 121:
                        $socket_type[$slot] = 'Băng (Tăng độ bền vật phẩm) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 122:
                        $socket_type[$slot] = 'Gió (Tự động hồi phục HP) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 123:
                        $socket_type[$slot] = 'Gió (Tăng HP tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 124:
                        $socket_type[$slot] = 'Gió (Tăng Mana tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 125:
                        $socket_type[$slot] = 'Gió (Tự động hồi phục Mana) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 126:
                        $socket_type[$slot] = 'Gió (Tăng AG tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 127:
                        $socket_type[$slot] = 'Gió (Tăng lượng AG) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 130:
                        $socket_type[$slot] = 'Sét (Tăng sát thương hoàn hảo) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 131:
                        $socket_type[$slot] = 'Sét (Tăng tỷ lệ sát thương hoàn hảo) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 132:
                        $socket_type[$slot] = 'Sét (Tăng sát thương chí mạng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 133:
                        $socket_type[$slot] = 'Sét (Tăng tỷ lệ sát thương chí mạng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 137:
                        $socket_type[$slot] = 'Đất (Tăng thể lực) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 151:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật (theo Level) + 400';
                        $socket[$slot] = 1;
                        break;
                    case 152:
                        $socket_type[$slot] = 'Lửa (Tăng tốc độ đánh) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 153:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 154:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật tối thiểu) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 155:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 156:
                        $socket_type[$slot] = 'Lửa (Giảm lượng AG khi dùng kỹ năng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 161:
                        $socket_type[$slot] = 'Nước (Tăng tỷ lệ phòng thủ) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 162:
                        $socket_type[$slot] = 'Nước (Tăng sức phòng thủ) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 163:
                        $socket_type[$slot] = 'Nước (Tăng sức phòng thủ Shield) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 164:
                        $socket_type[$slot] = 'Nước (Giảm sát thương) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 165:
                        $socket_type[$slot] = 'Nước (Phản hồi sát thương) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 167:
                        $socket_type[$slot] = 'Băng (Tăng khả năng hồi phục HP khi giết quái vật) + 51';
                        $socket[$slot] = 1;
                        break;
                    case 168:
                        $socket_type[$slot] = 'Băng (Tăng khả năng hồi phục Mana khi giết quái vật) + 51';
                        $socket[$slot] = 1;
                        break;
                    case 169:
                        $socket_type[$slot] = 'Băng (Tăng sức sát thương kỹ năng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 170:
                        $socket_type[$slot] = 'Băng (Tăng lực tấn công) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 171:
                        $socket_type[$slot] = 'Băng (Tăng độ bền vật phẩm) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 172:
                        $socket_type[$slot] = 'Gió (Tự động hồi phục HP) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 173:
                        $socket_type[$slot] = 'Gió (Tăng HP tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 174:
                        $socket_type[$slot] = 'Gió (Tăng Mana tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 175:
                        $socket_type[$slot] = 'Gió (Tự động hồi phục Mana) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 176:
                        $socket_type[$slot] = 'Gió (Tăng AG tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 177:
                        $socket_type[$slot] = 'Gió (Tăng lượng AG) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 180:
                        $socket_type[$slot] = 'Sét (Tăng sát thương hoàn hảo) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 181:
                        $socket_type[$slot] = 'Sét (Tăng tỷ lệ sát thương hoàn hảo) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 182:
                        $socket_type[$slot] = 'Sét (Tăng sát thương chí mạng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 183:
                        $socket_type[$slot] = 'Sét (Tăng tỷ lệ sát thương chí mạng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 187:
                        $socket_type[$slot] = 'Đất (Tăng thể lực) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 201:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật (theo Level) + 400';
                        $socket[$slot] = 1;
                        break;
                    case 202:
                        $socket_type[$slot] = 'Lửa (Tăng tốc độ đánh) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 203:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 204:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật tối thiểu) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 205:
                        $socket_type[$slot] = 'Lửa (Tăng tấn công/phép thuật) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 206:
                        $socket_type[$slot] = 'Lửa (Giảm lượng AG khi dùng kỹ năng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 211:
                        $socket_type[$slot] = 'Nước (Tăng tỷ lệ phòng thủ) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 212:
                        $socket_type[$slot] = 'Nước (Tăng sức phòng thủ) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 213:
                        $socket_type[$slot] = 'Nước (Tăng sức phòng thủ Shield) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 214:
                        $socket_type[$slot] = 'Nước (Giảm sát thương) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 215:
                        $socket_type[$slot] = 'Nước (Phản hồi sát thương) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 217:
                        $socket_type[$slot] = 'Băng (Tăng khả năng hồi phục HP khi giết quái vật) + 52';
                        $socket[$slot] = 1;
                        break;
                    case 218:
                        $socket_type[$slot] = 'Băng (Tăng khả năng hồi phục Mana khi giết quái vật) + 52';
                        $socket[$slot] = 1;
                        break;
                    case 219:
                        $socket_type[$slot] = 'Băng (Tăng sức sát thương kỹ năng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 220:
                        $socket_type[$slot] = 'Băng (Tăng lực tấn công) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 221:
                        $socket_type[$slot] = 'Băng (Tăng độ bền vật phẩm) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 222:
                        $socket_type[$slot] = 'Gió (Tự động hồi phục HP) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 223:
                        $socket_type[$slot] = 'Gió (Tăng HP tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 224:
                        $socket_type[$slot] = 'Gió (Tăng Mana tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 225:
                        $socket_type[$slot] = 'Gió (Tự động hồi phục Mana) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 226:
                        $socket_type[$slot] = 'Gió (Tăng AG tối đa) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 227:
                        $socket_type[$slot] = 'Gió (Tăng lượng AG) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 230:
                        $socket_type[$slot] = 'Sét (Tăng sát thương hoàn hảo) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 231:
                        $socket_type[$slot] = 'Sét (Tăng tỷ lệ sát thương hoàn hảo) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 232:
                        $socket_type[$slot] = 'Sét (Tăng sát thương chí mạng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 233:
                        $socket_type[$slot] = 'Sét (Tăng tỷ lệ sát thương chí mạng) + 1';
                        $socket[$slot] = 1;
                        break;
                    case 237:
                        $socket_type[$slot] = 'Đất (Tăng thể lực) + 1';
                        $socket[$slot] = 1;
                        break;
                }
            }
            if ($socket[$slot] == 1) $item_socket .= '<br>Socket ' . $slot . ': ' . $socket_type[$slot];
        }
        $item_harmony = '';
        if ($harmony < 32) $item_harmony .= 'Lực tấn công tồi thiểu + ';
        else if ($harmony < 48) $item_harmony .= 'Lực tấn công tồi đa + ';
        else if ($harmony < 64) $item_harmony .= 'Điểm Sức mạnh yêu cầu - ';
        else if ($harmony < 80) $item_harmony .= 'Điểm nhanh nhẹn yêu cầu - ';
        else if ($harmony < 96) $item_harmony .= 'Lực tấn công (tối đa, tối thiểu) + ';
        else if ($harmony < 112) $item_harmony .= 'Sát thương trọng kích + ';
        else if ($harmony < 128) $item_harmony .= 'Lực tấn công kĩ năng + ';
        else if ($harmony < 144) $item_harmony .= 'Tỷ lệ tấn công % + ';
        else if ($harmony < 160) $item_harmony .= 'Tỷ lệ SD + ';
        else if ($harmony < 176) $item_harmony .= 'Tỷ lệ loại bỏ SD + ';
        switch ($harmony) {
            case 16:
                $item_harmony .= '2';
                break;
            case 17:
                $item_harmony .= '3';
                break;
            case 18:
                $item_harmony .= '4';
                break;
            case 19:
                $item_harmony .= '5';
                break;
            case 20:
                $item_harmony .= '6';
                break;
            case 21:
                $item_harmony .= '7';
                break;
            case 22:
                $item_harmony .= '9';
                break;
            case 23:
                $item_harmony .= '11';
                break;
            case 24:
                $item_harmony .= '12';
                break;
            case 25:
                $item_harmony .= '14';
                break;
            case 26:
                $item_harmony .= '15';
                break;
            case 27:
                $item_harmony .= '16';
                break;
            case 28:
                $item_harmony .= '17';
                break;
            case 29:
                $item_harmony .= '20';
                break;
            case 30:
                $item_harmony .= '100000';
                break;
            case 31:
                $item_harmony .= '110000';
                break;
            case 32:
                $item_harmony .= '3';
                break;
            case 33:
                $item_harmony .= '4';
                break;
            case 34:
                $item_harmony .= '5';
                break;
            case 35:
                $item_harmony .= '6';
                break;
            case 36:
                $item_harmony .= '7';
                break;
            case 37:
                $item_harmony .= '8';
                break;
            case 38:
                $item_harmony .= '10';
                break;
            case 39:
                $item_harmony .= '12';
                break;
            case 40:
                $item_harmony .= '14';
                break;
            case 41:
                $item_harmony .= '17';
                break;
            case 42:
                $item_harmony .= '20';
                break;
            case 43:
                $item_harmony .= '23';
                break;
            case 44:
                $item_harmony .= '26';
                break;
            case 45:
                $item_harmony .= '29';
                break;
            case 46:
                $item_harmony .= '100000';
                break;
            case 47:
                $item_harmony .= '110000';
                break;
            case 48:
                $item_harmony .= '6';
                break;
            case 49:
                $item_harmony .= '8';
                break;
            case 50:
                $item_harmony .= '10';
                break;
            case 51:
                $item_harmony .= '12';
                break;
            case 52:
                $item_harmony .= '14';
                break;
            case 53:
                $item_harmony .= '16';
                break;
            case 54:
                $item_harmony .= '20';
                break;
            case 55:
                $item_harmony .= '23';
                break;
            case 56:
                $item_harmony .= '26';
                break;
            case 57:
                $item_harmony .= '29';
                break;
            case 58:
                $item_harmony .= '32';
                break;
            case 59:
                $item_harmony .= '35';
                break;
            case 60:
                $item_harmony .= '37';
                break;
            case 61:
                $item_harmony .= '40';
                break;
            case 62:
                $item_harmony .= '100000';
                break;
            case 63:
                $item_harmony .= '110000';
                break;
            case 64:
                $item_harmony .= '6';
                break;
            case 65:
                $item_harmony .= '8';
                break;
            case 66:
                $item_harmony .= '10';
                break;
            case 67:
                $item_harmony .= '12';
                break;
            case 68:
                $item_harmony .= '14';
                break;
            case 69:
                $item_harmony .= '16';
                break;
            case 70:
                $item_harmony .= '20';
                break;
            case 71:
                $item_harmony .= '23';
                break;
            case 72:
                $item_harmony .= '26';
                break;
            case 73:
                $item_harmony .= '29';
                break;
            case 74:
                $item_harmony .= '32';
                break;
            case 75:
                $item_harmony .= '35';
                break;
            case 76:
                $item_harmony .= '37';
                break;
            case 77:
                $item_harmony .= '40';
                break;
            case 78:
                $item_harmony .= '100000';
                break;
            case 79:
                $item_harmony .= '110000';
                break;
            case 80:
                $item_harmony .= '0';
                break;
            case 81:
                $item_harmony .= '0';
                break;
            case 82:
                $item_harmony .= '0';
                break;
            case 83:
                $item_harmony .= '0';
                break;
            case 84:
                $item_harmony .= '0';
                break;
            case 85:
                $item_harmony .= '0';
                break;
            case 86:
                $item_harmony .= '7';
                break;
            case 87:
                $item_harmony .= '8';
                break;
            case 88:
                $item_harmony .= '9';
                break;
            case 89:
                $item_harmony .= '11';
                break;
            case 90:
                $item_harmony .= '12';
                break;
            case 91:
                $item_harmony .= '14';
                break;
            case 92:
                $item_harmony .= '16';
                break;
            case 93:
                $item_harmony .= '19';
                break;
            case 94:
                $item_harmony .= '0';
                break;
            case 95:
                $item_harmony .= '0';
                break;
            case 96:
                $item_harmony .= '0';
                break;
            case 97:
                $item_harmony .= '0';
                break;
            case 98:
                $item_harmony .= '0';
                break;
            case 99:
                $item_harmony .= '0';
                break;
            case 100:
                $item_harmony .= '0';
                break;
            case 101:
                $item_harmony .= '0';
                break;
            case 102:
                $item_harmony .= '12';
                break;
            case 103:
                $item_harmony .= '14';
                break;
            case 104:
                $item_harmony .= '16';
                break;
            case 105:
                $item_harmony .= '18';
                break;
            case 106:
                $item_harmony .= '20';
                break;
            case 107:
                $item_harmony .= '22';
                break;
            case 108:
                $item_harmony .= '24';
                break;
            case 109:
                $item_harmony .= '30';
                break;
            case 110:
                $item_harmony .= '0';
                break;
            case 111:
                $item_harmony .= '0';
                break;
            case 112:
                $item_harmony .= '0';
                break;
            case 113:
                $item_harmony .= '0';
                break;
            case 114:
                $item_harmony .= '0';
                break;
            case 115:
                $item_harmony .= '0';
                break;
            case 116:
                $item_harmony .= '0';
                break;
            case 117:
                $item_harmony .= '0';
                break;
            case 118:
                $item_harmony .= '0';
                break;
            case 119:
                $item_harmony .= '0';
                break;
            case 120:
                $item_harmony .= '0';
                break;
            case 121:
                $item_harmony .= '12';
                break;
            case 122:
                $item_harmony .= '14';
                break;
            case 123:
                $item_harmony .= '16';
                break;
            case 124:
                $item_harmony .= '18';
                break;
            case 125:
                $item_harmony .= '22';
                break;
            case 126:
                $item_harmony .= '0';
                break;
            case 127:
                $item_harmony .= '0';
                break;
            case 128:
                $item_harmony .= '0';
                break;
            case 129:
                $item_harmony .= '0';
                break;
            case 130:
                $item_harmony .= '0';
                break;
            case 131:
                $item_harmony .= '0';
                break;
            case 132:
                $item_harmony .= '0';
                break;
            case 133:
                $item_harmony .= '0';
                break;
            case 134:
                $item_harmony .= '0';
                break;
            case 135:
                $item_harmony .= '0';
                break;
            case 136:
                $item_harmony .= '0';
                break;
            case 137:
                $item_harmony .= '5';
                break;
            case 138:
                $item_harmony .= '7';
                break;
            case 139:
                $item_harmony .= '9';
                break;
            case 140:
                $item_harmony .= '11';
                break;
            case 141:
                $item_harmony .= '14';
                break;
            case 142:
                $item_harmony .= '0';
                break;
            case 143:
                $item_harmony .= '0';
                break;
            case 144:
                $item_harmony .= '0';
                break;
            case 145:
                $item_harmony .= '0';
                break;
            case 146:
                $item_harmony .= '0';
                break;
            case 147:
                $item_harmony .= '0';
                break;
            case 148:
                $item_harmony .= '0';
                break;
            case 149:
                $item_harmony .= '0';
                break;
            case 150:
                $item_harmony .= '0';
                break;
            case 151:
                $item_harmony .= '0';
                break;
            case 152:
                $item_harmony .= '0';
                break;
            case 153:
                $item_harmony .= '3';
                break;
            case 154:
                $item_harmony .= '5';
                break;
            case 155:
                $item_harmony .= '7';
                break;
            case 156:
                $item_harmony .= '9';
                break;
            case 157:
                $item_harmony .= '10';
                break;
            case 158:
                $item_harmony .= '0';
                break;
            case 159:
                $item_harmony .= '0';
                break;
            case 160:
                $item_harmony .= '0';
                break;
            case 161:
                $item_harmony .= '0';
                break;
            case 162:
                $item_harmony .= '0';
                break;
            case 163:
                $item_harmony .= '0';
                break;
            case 164:
                $item_harmony .= '0';
                break;
            case 165:
                $item_harmony .= '0';
                break;
            case 166:
                $item_harmony .= '0';
                break;
            case 167:
                $item_harmony .= '0';
                break;
            case 168:
                $item_harmony .= '0';
                break;
            case 169:
                $item_harmony .= '0';
                break;
            case 170:
                $item_harmony .= '0';
                break;
            case 171:
                $item_harmony .= '0';
                break;
            case 172:
                $item_harmony .= '0';
                break;
            case 173:
                $item_harmony .= '10';
                break;
            case 174:
                $item_harmony .= '0';
                break;
            case 175:
                $item_harmony .= '0';
                break;
        }
        $item_read = $this->ItemsData($itemdata_arr, $id, $group, $item_level);
        // Tra ID và Group Item để lấy thông tin từ Items_Data.txt
        $item_image = $item_read['image'];
        $item_name = $item_read['name'];
        $item_name_en = $item_read['name_en'];
        $item_x = $item_read['x'];
        $item_y = $item_read['y'];
        $item_group = $item_read['groupid'];
        $dw = $item_read['dw'];
        $dk = $item_read['dk'];
        $elf = $item_read['elf'];
        $mg = $item_read['mg'];
        $dl = $item_read['dl'];
        $sum = $item_read['sum'];
        $rf = $item_read['rf'];
        if ($ancient == 5) $ancient_set = $item_read['set1'];
        else if ($ancient == 10) $ancient_set = $item_read['set2'];
        //Sử lí màu Tên Item
        $color = '#FFFFFF'; // White -> Normal Item
        if (($option > 1) || ($luck != '')) $color = '#8CB0EA';
        if ($item_level > 6) $color = '#F4CB3F';
        $ExclAnci = 0;
        if ($item_type == 6) {//Sói tinh
            $color = '#8CB0EA';
            if ($iopx1 == 1) {
                $item_name .= ' + Tấn công';
                $ExclAnci = 1;
            }
            if ($iopx2 == 1) {
                $item_name .= ' + Phòng thủ';
                $ExclAnci = 1;
            }
            if ($iopx3 == 1) {
                $item_name .= ' Hoàng Kim';
                $color = '#F4CB3F';
                $ExclAnci = 1;
            }
        } else if ($item_exc != '') {//Item Excellent
            //Item Harmony
            if ($harmony > 0) $item_name = 'Tử Âm ' . $item_name;
            $item_name = 'Hoàn Hảo ' . $item_name;
            $color = '#2FF387';
            $ExclAnci = 1;
        }
        // Item Thần
        if ($ancient > 0) {
            $item_name = 'SET Thần' . ' ' . $ancient_set . ' ' . $item_name;
            $color = '#2347F3';
            $ExclAnci = 2;
        }
        // Item Socket
        if ($item_socket != '') {
            $color = '#AA1EAA';
            $is_socket = 1;
        } else $is_socket = 0;
        if ($nocolor) $color = '#F4CB3F';
        // Sử lí thông tin Item
        switch ($ExclAnci) {
            case 1:
                if (file_exists('items/EXE/' . $item_image . '.gif')) $item_image = "EXE/" . $item_image;
                else $item_image = $item_image;
                break;
            case 2:
                if (file_exists('items/ANC/' . $item_image . '.gif')) $item_image = "ANC/" . $item_image;
                else $item_image = $item_image;
                break;
            default:
                $item_image = $item_image;
                break;
        }
        if($dk || $dw || $elf || $mg || $dl || $sum || $rf) {
            $class_use = '<br>Dùng cho Class: ';
        } else {
            $class_use = '';
        }
        if ($dw) {
            $class_use .= 'DW, ';
        }
        if ($dk) {
            $class_use .= 'DK, ';
        }
        if ($elf) {
            $class_use .= 'ELF, ';
        }
        if ($mg) {
            $class_use .= 'MG, ';
        }
        if ($dl) {
            $class_use .= 'DL, ';
        }
        if ($sum) {
            $class_use .= 'SUM, ';
        }
        if ($rf) {
            $class_use .= 'RF, ';
        }
        //$item_image = ItemImage($id,$group,$ExclAnci,$item_level);
        if ($item_level == 0 || $durability == 0) $item_level = '';
        else $item_level = ' +' . $item_level;
        $_serial = $serial;
        $serial = 'Serial: ' . $serial . '<br>';
        $durability = 'Độ bền: ' . $durability . '<br>';
        if ($harmony > 0) $item_harmony = '<font color=#C8C800>' . $item_harmony . '</font><br>'; else $item_harmony = '';
        $item_exc = '<font color=#2FF387>' . $item_exc . '</font><br>';
        $item_socket = '<font color=#AA1EAA>' . $item_socket . '</font><br>';
        $item_info = '<center><strong><span style=color:#FFFFFF><font color=' . $color . '>' . $item_name . $item_level . '</font></strong><br>'
            . $durability
            . $luck
            . $skill
            . $item_option
            . $item_harmony
            . $item_exc
            . $item_socket
            . $class_use .'</span></center>';
        if (strlen($luck) > 0) $is_luck = 1;
        else $is_luck = 0;
        if (strlen($skill) > 0) $is_skill = 1;
        else $is_skill = 0;


        $output = array(
            'info' => $item_info,
            'name' => $item_name,
            'name_en' => $item_name_en,
            'image' => $item_image,
            'level' => $item__level,
            'is_limited' => $is_limited,
            'opt' => $option,
            'luck' => $is_luck,           // 1: Luck, 0: Non Luck
            'skill' => $is_skill,
            'dur' => $dur,
            'exc_opt' => $exc_opt,
            'exc_total' => $exc_total,
            'exc_1' => $iopx1,
            'exc_2' => $iopx2,
            'exc_3' => $iopx3,
            'exc_4' => $iopx4,
            'exc_5' => $iopx5,
            'exc_6' => $iopx6,
            'serial' => $_serial,
            'x' => $item_x,
            'y' => $item_y,
            'item_code' => $item_code,
            'exc_anc' => $ExclAnci,       // 1: Exc, 2: Anci
            'socket' => $is_socket,       // 1: Socket, 0: Non Socket
            'id' => $id,
            'type' => $item_type,
            'group' => $group,
            'item_group' => $item_group,
            'dw' => $dw,
            'dk' => $dk,
            'elf' => $elf,
            'mg' => $mg,
            'dl' => $dl,
            'sum' => $sum,
            'rf' => $rf
        );
        return $output;
    }

    function CheckSlot($itemdata_arr, $data, $itemX, $itemY)
    {
        $no_item = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
        $data_slot = floor(strlen($data) / 32);
        // Kiem tra o Item
        $slot_flag = 0;
        $line_y = 0;

        while ($slot_flag < $data_slot) {
            ++$line_y;
            for ($line_x = 1; $line_x <= 8; ++$line_x) {
                ++$slot_flag;
                $o_status[$line_x][$line_y] = 0;
            }
        }

        for ($y = 1; $y <= $line_y; ++$y) {
            for ($x = 1; $x <= 8; ++$x) {
                $o[$x][$y] = substr($data, (($y - 1) * 8 + $x - 1) * 32, 32);

                if ($o[$x][$y] != $no_item) {
                    $item_getcode = $this->GetCode($o[$x][$y]);
                    $item_data = $this->ItemsData($itemdata_arr, $item_getcode['id'], $item_getcode['group'], $item_getcode['Lvl']);
                    $item_data_x = $item_data['x'];
                    $item_data_y = $item_data['y'];

                    for ($y_status = $y; $y_status <= ($y + $item_data_y - 1); ++$y_status) {
                        for ($x_status = $x; $x_status <= ($x + $item_data_x - 1); ++$x_status) {
                            $o_status[$x_status][$y_status] = 1;
                        }
                    }
                }
            }
        }
        // End Kiem tra o Item

        $o_accept_x = 0;
        $o_accept_y = 0;
        for ($y = 1; $y <= ($line_y - $itemY + 1); ++$y) {
            for ($x = 1; $x <= (8 - $itemX + 1); ++$x) {
                $o_accept_flag = 1;
                // Kiem tra nhung o lien quan
                for ($y_check = $y; $y_check <= ($y + $itemY - 1); ++$y_check) {
                    for ($x_check = $x; $x_check <= ($x + $itemX - 1); ++$x_check) {
                        if ($o_status[$x_check][$y_check] == 1) {
                            $o_accept_flag = 0;
                            break;
                        }
                    }
                    if ($o_accept_flag == 0) break;
                }
                if ($o_accept_flag == 1) {
                    $o_accept_x = $x;
                    $o_accept_y = $y;
                    break;
                }
            }
            if ($o_accept_flag == 1) break;
        }

        if ($o_accept_x != 0 && $o_accept_y != 0) {
            $slot_accept = ($o_accept_y - 1) * 8 + $o_accept_x;
        } else {
            $slot_accept = 0;
        }

        return $slot_accept;

    }
}
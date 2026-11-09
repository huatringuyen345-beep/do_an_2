<?php
/* Nội dung mẫu; xác nhận chính sách và thông tin trước khi đưa website vào dùng thật. */
$payments = [
    'quay' => [
        'ten' => 'Thanh toán tại quầy vé',
        'mota' => 'Nhân viên sẽ xác nhận thông tin và hướng dẫn thanh toán.',
        'icon' => 'wallet',
    ],
    'ck' => [
        'ten' => 'Chuyển khoản ngân hàng',
        'mota' => 'Chỉ chuyển khoản sau khi nhân viên xác nhận và cung cấp thông tin nhận tiền.',
        'icon' => 'bank',
    ],
    'vi' => [
        'ten' => 'Ví điện tử (MoMo, ZaloPay)',
        'mota' => 'Nhân viên sẽ xác nhận phương thức thanh toán trước khi bạn thanh toán.',
        'icon' => 'phone',
    ],
    'the' => [
        'ten' => 'Thẻ ATM / Visa / Mastercard',
        'mota' => 'Website chưa thu tiền trực tuyến; nhân viên sẽ xác nhận cách thanh toán.',
        'icon' => 'card',
    ],
];

$news_types = [
    'tin-tuc' => 'Tin tức',
    'su-kien' => 'Sự kiện',
    'khuyen-mai' => 'Khuyến mãi',
    'thong-bao' => 'Thông báo',
];

$news = [
    [
        'id' => 1, 'loai' => 'khuyen-mai', 'ngay' => '2026-10-05',
        'ket_thuc' => '2026-11-30',
        'tieu_de' => 'Giảm 10% vé giường nằm đi TP. Hồ Chí Minh khi đặt online',
        'tom_tat' => 'Áp dụng cho các chuyến khởi hành từ thứ Hai đến thứ Năm, đặt trước ít nhất 24 giờ.',
        'noi_dung' => [
            'Từ nay đến hết tháng 11, khách đặt vé giường nằm tuyến Bạc Liêu – TP. Hồ Chí Minh trên website được giảm 10% giá vé.',
            'Chương trình áp dụng cho các chuyến khởi hành từ thứ Hai đến thứ Năm. Vé cần được đặt trước giờ xe chạy ít nhất 24 giờ.',
            'Chương trình không cộng dồn với các ưu đãi khác. Liên hệ hotline để được hướng dẫn chi tiết.',
        ],
    ],
    [
        'id' => 2, 'loai' => 'thong-bao', 'ngay' => '2026-10-03',
        'tieu_de' => 'Tăng chuyến đi Đà Lạt và Nha Trang dịp cuối năm',
        'tom_tat' => 'Bổ sung thêm giờ chạy buổi tối cho hai tuyến đường dài từ giữa tháng 12.',
        'noi_dung' => [
            'Để đáp ứng nhu cầu đi lại dịp cuối năm, Tài Nguyên bổ sung thêm chuyến buổi tối cho tuyến Đà Lạt và Nha Trang.',
            'Lịch chạy chi tiết sẽ được cập nhật trên trang Lịch trình. Quý khách nên đặt vé sớm để chọn được chỗ ngồi mong muốn.',
        ],
    ],
    [
        'id' => 3, 'loai' => 'tin-tuc', 'ngay' => '2026-09-28',
        'tieu_de' => 'Xe trung chuyển đón khách tận nơi trong nội ô Bạc Liêu',
        'tom_tat' => 'Khách đặt vé online có thể đăng ký đón tại nhà ngay trong bước điền thông tin.',
        'noi_dung' => [
            'Từ nay, khi đặt vé trên website, quý khách chỉ cần đánh dấu "Đăng ký xe trung chuyển" và nhập địa chỉ đón.',
            'Nhân viên sẽ gọi xác nhận thời gian đón trước giờ xe chạy. Dịch vụ áp dụng trong khu vực nội ô và các xã lân cận.',
        ],
    ],
    [
        'id' => 4, 'loai' => 'thong-bao', 'ngay' => '2026-09-20',
        'tieu_de' => 'Tuyển lái xe và nhân viên phòng vé',
        'tom_tat' => 'Tài Nguyên đang cần bổ sung nhân sự cho các tuyến đi TP. Hồ Chí Minh và Đà Lạt.',
        'noi_dung' => [
            'Công ty đang tuyển lái xe khách hạng D và nhân viên phòng vé làm việc tại Bạc Liêu.',
            'Xem chi tiết vị trí và nộp hồ sơ tại trang Tuyển dụng.',
        ],
    ],
    [
        'id' => 5, 'loai' => 'khuyen-mai', 'ngay' => '2026-09-12',
        'tieu_de' => 'Ưu đãi cước gửi hàng cho khách thường xuyên',
        'tom_tat' => 'Giảm cước cho khách gửi hàng từ 4 lần mỗi tháng trên cùng một tuyến.',
        'noi_dung' => [
            'Khách hàng gửi hàng thường xuyên trên cùng một tuyến được áp dụng bảng cước ưu đãi riêng.',
            'Vui lòng liên hệ phòng điều hành để đăng ký và nhận bảng giá.',
        ],
    ],
    [
        'id' => 6, 'loai' => 'su-kien', 'ngay' => '2026-08-30',
        'tieu_de' => 'Hướng dẫn đặt vé online trong 3 bước',
        'tom_tat' => 'Chọn chuyến, chọn chỗ, điền thông tin. Không cần tạo tài khoản.',
        'noi_dung' => [
            'Vào trang Đặt vé, chọn tuyến, ngày đi và giờ khởi hành.',
            'Chọn chỗ ngồi hoặc giường trên sơ đồ xe, sau đó điền họ tên và số điện thoại.',
            'Chọn hình thức thanh toán và xác nhận. Mã vé sẽ hiển thị ngay trên màn hình.',
        ],
    ],
];
$news = array_values(array_filter($news, function ($item) {
    return empty($item['ket_thuc']) || $item['ket_thuc'] >= date('Y-m-d');
}));

function find_news($id)
{
    global $news;
    foreach ($news as $item) {
        if ((int)$item['id'] === (int)$id) {
            return $item;
        }
    }
    return null;
}

$jobs = [
    [
        'ma' => 'lai-xe', 'ten' => 'Lái xe khách', 'sl' => 4, 'noi' => 'Bạc Liêu', 'luong' => 'Từ 12 – 18 triệu',
        'mota' => 'Điều khiển xe khách các tuyến đi TP. Hồ Chí Minh, Bình Dương, Vũng Tàu và Đà Lạt.',
        'yeu_cau' => ['Bằng lái hạng D còn hạn', 'Từ 3 năm kinh nghiệm lái xe khách', 'Sức khỏe tốt, có thể đi tuyến đường dài'],
    ],
    [
        'ma' => 'phu-xe', 'ten' => 'Phụ xe', 'sl' => 3, 'noi' => 'Bạc Liêu', 'luong' => 'Từ 7 – 10 triệu',
        'mota' => 'Hỗ trợ khách lên xuống, sắp xếp hành lý và hàng hóa gửi theo xe.',
        'yeu_cau' => ['Nhanh nhẹn, giao tiếp lịch sự', 'Chấp nhận đi tuyến dài ngày', 'Ưu tiên có kinh nghiệm'],
    ],
    [
        'ma' => 'phong-ve', 'ten' => 'Nhân viên phòng vé', 'sl' => 2, 'noi' => 'Bạc Liêu', 'luong' => 'Từ 8 – 10 triệu',
        'mota' => 'Bán vé, tư vấn tuyến và giờ chạy, tiếp nhận hàng gửi tại văn phòng.',
        'yeu_cau' => ['Tốt nghiệp trung cấp trở lên', 'Sử dụng thành thạo máy tính', 'Có thể làm theo ca'],
    ],
    [
        'ma' => 'web', 'ten' => 'Nhân viên chăm sóc khách hàng online', 'sl' => 1, 'noi' => 'Bạc Liêu', 'luong' => 'Thỏa thuận',
        'mota' => 'Trả lời khách qua điện thoại và website, xác nhận vé đặt online.',
        'yeu_cau' => ['Giọng nói rõ ràng, kiên nhẫn', 'Biết sử dụng máy tính văn phòng', 'Ưu tiên người biết quản trị website'],
    ],
];

$faqs = [
    ['q' => 'Tôi cần đến sớm bao lâu trước giờ xe chạy?', 'a' => 'Quý khách nên có mặt tại điểm đón trước giờ khởi hành 20 đến 30 phút để làm thủ tục và gửi hành lý.'],
    ['q' => 'Xe ghế ngồi và giường nằm khác nhau thế nào?', 'a' => 'Xe ghế ngồi phù hợp các tuyến ngắn trong khu vực miền Tây. Xe giường nằm có hai tầng, phù hợp các tuyến dài đi TP. Hồ Chí Minh, Đà Lạt, Nha Trang.'],
    ['q' => 'Tôi có thể đổi hoặc hủy vé không?', 'a' => 'Quý khách có thể đổi hoặc hủy vé trước giờ xe chạy. Mức phí phụ thuộc thời điểm hủy, xem bảng bên dưới hoặc gọi hotline.'],
    ['q' => 'Trẻ em có phải mua vé không?', 'a' => 'Trẻ em dưới 5 tuổi ngồi cùng người lớn được miễn vé. Từ 5 tuổi trở lên mua vé như người lớn.'],
    ['q' => 'Hành lý được mang theo bao nhiêu?', 'a' => 'Mỗi khách được mang theo hành lý tối đa 20 kg miễn cước. Phần vượt quá tính theo bảng cước gửi hàng.'],
    ['q' => 'Tôi muốn gửi hàng thì làm thế nào?', 'a' => 'Mang hàng đến văn phòng Tài Nguyên hoặc gọi hotline để xe trung chuyển đến lấy. Xem thêm tại trang Dịch vụ.'],
];

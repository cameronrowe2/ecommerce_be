<?php

class email
{

    public static function sendVerificationEmail($email, $email_hash)
    {
        // send validation email
        $to      = $email; // Send email to our user
        $subject = 'Email Verification'; // Give the email a subject
        $message = '

        Thanks for signing up!
        Your account has been created, click the link below to activate your account!

        Please click this link to activate your account:
        http://localhost:3000/verifyemail/' . $email_hash . '

        ';

        $headers = 'From:cameronroweau@gmail.com' . "\r\n"; // Set from headers
        if (!mail($to, $subject, $message, $headers)) {
            return false;
        }

        return true;
    }

    public static function sendOrderEmail($email, $order_id, $cart_data, $delivery_price, $total_price)
    {
        // send validation email
        $to      = $email; // Send email to our user
        $subject = 'Order Email'; // Give the email a subject
        $message = '

        <p>Thanks for your order!</p>

        <p>Order #' . $order_id . '</p>

        <table>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>';

        foreach ($cart_data['cart_items'] as $cart_item) {
            $product_price = $cart_item['product_price'] * $cart_item['quantity'];

            $message .= '
            <tr>
                <td>' . $cart_item['product_title'] . '</td>
                <td>$' . $cart_item['product_price'] . '</td>
                <td>' . $cart_item['quantity'] . '</td>
                <td>$' . $product_price . '</td>
            </tr>';
        }

        $message .= '
            <tr>
                <td>Delivery</td>
                <td></td>
                <td></td>
                <td>$' . $delivery_price . '</td>
            </tr>
            <tr>
                <td>Total Price</td>
                <td></td>
                <td></td>
                <td>$' . $total_price . '</td>
            </tr>
        ';


        $headers = 'From:cameronroweau@gmail.com' . "\r\n"; // Set from headers
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        if (!mail($to, $subject, $message, $headers)) {
            return false;
        }

        return true;
    }
}

<?php
declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

final class DineroYFormatoTest extends TestCase
{
    /** // P01 */
    public function test_dinero_formatea_con_dos_decimales(): void
    {
        $this->assertSame('S/ 4.90', dinero(4.9));
        $this->assertSame('S/ 0.00', dinero(0.0));
    }

    /** // P02 */
    public function test_escape_html_previene_xss_basico(): void
    {
        $this->assertSame('&lt;script&gt;', e('<script>'));
        $this->assertSame('', e(null));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Merchant;
use App\Models\Product;
use App\Services\ImageProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImageProcessorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_resizes_to_webp_and_makes_variants(): void
    {
        Storage::fake('public');
        $path = app(ImageProcessor::class)->store(UploadedFile::fake()->image('big.jpg', 2400, 1600), 'products');

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);
        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame(1200, max($w, $h));
        foreach ([64, 400, 800] as $v) {
            Storage::disk('public')->assertExists(str_replace('.webp', "-{$v}.webp", $path));
        }
    }

    #[Test]
    public function it_rejects_a_fake_image_by_real_mime(): void
    {
        Storage::fake('public');
        $this->expectException(InvalidArgumentException::class);
        app(ImageProcessor::class)->store(UploadedFile::fake()->createWithContent('x.jpg', '<?php echo 1;'), 'products');
    }

    #[Test]
    public function a_merchant_can_upload_a_product_image(): void
    {
        Storage::fake('public');
        $m = Merchant::factory()->create();

        $this->actingAs($m->user)->post('/dashboard/products', [
            'name' => 'منتج', 'image' => UploadedFile::fake()->image('a.png', 800, 800),
        ])->assertRedirect();

        $this->assertNotNull(Product::where('merchant_id', $m->id)->first()->image_path);
    }
}

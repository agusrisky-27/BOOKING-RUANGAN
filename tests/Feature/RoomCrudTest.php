<?php

namespace Tests\Feature;

use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Facility;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $sarpras;

    protected Facility $facility;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sarpras = User::create([
            'name' => 'Sarpras Admin',
            'username' => 'sarpras',
            'password' => bcrypt('password'),
            'role' => UserRole::SARPRAS,
            'is_active' => true,
        ]);

        $this->facility = Facility::create(['name' => 'Proyektor 4K']);
    }

    public function test_sarpras_can_create_room_with_facility(): void
    {
        $this->actingAs($this->sarpras);

        $response = $this->post(route('sarpras.rooms.store'), [
            'code' => 'LAB-AI',
            'name' => 'Lab Artificial Intelligence',
            'building' => 'Gedung Rektorat Lt. 3',
            'capacity' => 30,
            'status' => RoomStatus::AKTIF->value,
            'description' => 'Laboratorium komputasi berkinerja tinggi.',
            'facilities' => [$this->facility->id],
            'facility_quantities' => [$this->facility->id => 1],
            'facility_notes' => [$this->facility->id => 'Baru diinstalasi'],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('rooms', [
            'code' => 'LAB-AI',
            'name' => 'Lab Artificial Intelligence',
        ]);

        $room = Room::where('code', 'LAB-AI')->first();
        $this->assertTrue($room->facilities->contains($this->facility->id));
    }

    public function test_sarpras_can_update_room(): void
    {
        $room = Room::create([
            'code' => 'R201',
            'name' => 'Ruang 201',
            'building' => 'Gedung Utama',
            'capacity' => 35,
            'status' => RoomStatus::AKTIF,
        ]);

        $this->actingAs($this->sarpras);

        $response = $this->put(route('sarpras.rooms.update', $room), [
            'code' => 'R201',
            'name' => 'Ruang 201 (Renovasi)',
            'building' => 'Gedung Utama',
            'capacity' => 45,
            'status' => RoomStatus::PERAWATAN->value,
        ]);

        $response->assertSessionHasNoErrors();
        $room->refresh();
        $this->assertEquals('Ruang 201 (Renovasi)', $room->name);
        $this->assertEquals(45, $room->capacity);
        $this->assertEquals(RoomStatus::PERAWATAN, $room->status);
    }

    public function test_sarpras_can_soft_delete_room(): void
    {
        $room = Room::create([
            'code' => 'R999',
            'name' => 'Ruang Sementara',
            'building' => 'Gedung Darurat',
            'capacity' => 20,
            'status' => RoomStatus::NONAKTIF,
        ]);

        $this->actingAs($this->sarpras);

        $response = $this->delete(route('sarpras.rooms.destroy', $room));
        $response->assertSessionHasNoErrors();

        $this->assertSoftDeleted('rooms', ['id' => $room->id]);
    }
}

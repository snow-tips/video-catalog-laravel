<?php

declare(strict_types=1);

namespace Tests\Traits;

use Exception;
use Illuminate\Foundation\Testing\TestResponse;

trait TestSaves
{
  protected abstract function model();

  protected abstract function routeStore();
  
  protected abstract function routeUpdate();

  protected function assertStore(array $sendData, array $testDatabase, array $testResponseData = null): TestResponse
  {
    /** @var TestResponse $response */
    $response = $this->json('POST', $this->routeStore(), $sendData);

    if ($response->status() !== 201) {
      throw new Exception("Response status must be 201, given {$response->status()}:\n{$response->content()}");
    }

    $this->assertInDatabase($response, $testDatabase);
    $this->assertJsonResponseContent($response, $testDatabase, $testResponseData);

    return $response;
  }

  protected function assertUpdate(array $sendData, array $testDatabase, array $testResponseData = null): TestResponse
  {
    /** @var TestResponse $response */
    $response = $this->json('PUT', $this->routeUpdate(), $sendData);

    if ($response->status() !== 200) {
      throw new Exception("Response status must be 200, given {$response->status()}:\n{$response->content()}");
    }

    $this->assertInDatabase($response, $testDatabase);
    $this->assertJsonResponseContent($response, $testDatabase, $testResponseData);

    return $response;
  }

  private function assertInDatabase(TestResponse $response, array $testDatabase)
  {
    $model = $this->model();
    $table = (new $model)->getTable();
    $this->assertDatabaseHas($table, $testDatabase + ['id' => $response->json('id')]);
  }

  private function assertJsonResponseContent(TestResponse $response, array $testDatabase, array $testResponseData = null)
  {
    $testResponse = $testResponseData ?? $testDatabase;
    $response->assertJsonFragment($testResponse + ['id' => $response->json('id')]);
  }
}

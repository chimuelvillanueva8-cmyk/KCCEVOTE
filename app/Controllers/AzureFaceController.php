<?php

namespace App\Controllers;

class AzureFaceController extends BaseController
{
    public function detect()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
        }

        $body = $this->request->getJSON(true);
        if (empty($body)) {
            $body = json_decode($this->request->getBody(), true) ?: [];
        }

        $imageBase64 = $body['image_base64'] ?? null;
        if (empty($imageBase64)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No image provided'
            ]);
        }

        $imageBytes = $this->extractImageBytes($imageBase64);
        if ($imageBytes === false || $imageBytes === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid image payload'
            ]);
        }

        $endpoint = env('AZURE_FACE_ENDPOINT');
        $key = env('AZURE_FACE_KEY');

        if (empty($endpoint) || empty($key)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Azure Face API is not configured. Set AZURE_FACE_ENDPOINT and AZURE_FACE_KEY in your environment.'
            ]);
        }

        $response = $this->callAzureApi(
            rtrim($endpoint, '/') . '/face/v1.0/detect?returnFaceId=true&returnFaceLandmarks=false&returnFaceAttributes=age,gender,headPose',
            $imageBytes,
            'application/octet-stream',
            $key
        );

        if (!$response['success']) {
            return $this->response->setStatusCode($response['http_code'] ?: 500)->setJSON([
                'success' => false,
                'message' => 'Azure Face API request failed',
                'details' => $response['body'] ?: 'No response from Azure'
            ]);
        }

        $decoded = json_decode($response['body'], true);
        if (!is_array($decoded) || empty($decoded)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No face detected in the uploaded image'
            ]);
        }

        $face = $decoded[0] ?? null;
        if (!$face || empty($face['faceId'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No face detected in the uploaded image'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'face_id' => $face['faceId'],
            'face_rectangle' => $face['faceRectangle'] ?? null,
            'face_attributes' => $face['faceAttributes'] ?? null,
            'captured_at' => date('c')
        ]);
    }

    public function verify()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
        }

        $body = $this->request->getJSON(true);
        if (empty($body)) {
            $body = json_decode($this->request->getBody(), true) ?: [];
        }

        $faceId1 = $body['face_id_1'] ?? null;
        $faceId2 = $body['face_id_2'] ?? null;
        if (empty($faceId1) || empty($faceId2)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Both face IDs are required'
            ]);
        }

        $endpoint = env('AZURE_FACE_ENDPOINT');
        $key = env('AZURE_FACE_KEY');
        if (empty($endpoint) || empty($key)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Azure Face API is not configured.'
            ]);
        }

        $payload = json_encode([
            'faceId1' => $faceId1,
            'faceId2' => $faceId2,
        ]);

        $response = $this->callAzureApi(
            rtrim($endpoint, '/') . '/face/v1.0/verify',
            $payload,
            'application/json',
            $key
        );

        if (!$response['success']) {
            return $this->response->setStatusCode($response['http_code'] ?: 500)->setJSON([
                'success' => false,
                'message' => 'Azure Face verification failed',
                'details' => $response['body'] ?: 'No response from Azure'
            ]);
        }

        $decoded = json_decode($response['body'], true);
        return $this->response->setJSON([
            'success' => true,
            'is_identical' => $decoded['isIdentical'] ?? false,
            'confidence' => $decoded['confidence'] ?? null,
            'raw' => $decoded
        ]);
    }

    private function callAzureApi(string $url, $payload, string $contentType, string $key): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: ' . $contentType,
            'Ocp-Apim-Subscription-Key: ' . $key,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $body !== false && $httpCode < 400,
            'http_code' => $httpCode,
            'body' => $body === false ? '' : $body,
        ];
    }

    private function extractImageBytes(string $imageBase64)
    {
        $imageData = preg_replace('/^data:image\/[^;]+;base64,/', '', $imageBase64);
        $imageData = str_replace(['-', '_'], ['+', '/'], $imageData);

        return base64_decode($imageData, true);
    }
}

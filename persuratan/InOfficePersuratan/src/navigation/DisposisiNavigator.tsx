import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import DisposisiListScreen from '../screens/DisposisiListScreen';
import DisposisiDetailScreen from '../screens/DisposisiDetailScreen';
import DisposisiCreateScreen from '../screens/DisposisiCreateScreen';
import DisposisiForwardScreen from '../screens/DisposisiForwardScreen';
import DisposisiLaporanScreen from '../screens/DisposisiLaporanScreen';
import DisposisiTanggapanScreen from '../screens/DisposisiTanggapanScreen';

export type DisposisiStackParamList = {
  DisposisiList: undefined;
  DisposisiDetail: { id: number };
  DisposisiCreate: { surat_masuk_id?: number };
  DisposisiForward: { id: number };
  DisposisiLaporan: { id: number };
  DisposisiTanggapan: { id: number };
};

const Stack = createNativeStackNavigator<DisposisiStackParamList>();

export default function DisposisiNavigator() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerShown: false,
        animation: 'slide_from_right',
      }}
    >
      <Stack.Screen name="DisposisiList" component={DisposisiListScreen} />
      <Stack.Screen name="DisposisiDetail" component={DisposisiDetailScreen} />
      <Stack.Screen name="DisposisiCreate" component={DisposisiCreateScreen} />
      <Stack.Screen name="DisposisiForward" component={DisposisiForwardScreen} />
      <Stack.Screen name="DisposisiLaporan" component={DisposisiLaporanScreen} />
      <Stack.Screen name="DisposisiTanggapan" component={DisposisiTanggapanScreen} />
    </Stack.Navigator>
  );
}

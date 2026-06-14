import React from 'react';
import { StyleSheet, View } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';
import { useTheme } from '../theme/ThemeContext';
import DashboardScreen from '../screens/DashboardScreen';
import SuratMasukNavigator from './SuratMasukNavigator';
import DisposisiNavigator from './DisposisiNavigator';
import NotifikasiScreen from '../screens/NotifikasiScreen';
import ProfilScreen from '../screens/ProfilScreen';

export type MainTabParamList = {
  DashboardTab: undefined;
  SuratMasukTab: undefined;
  DisposisiTab: undefined;
  NotifikasiTab: undefined;
  ProfilTab: undefined;
};

const Tab = createBottomTabNavigator<MainTabParamList>();

interface TabBarIconProps {
  focused: boolean;
  name: string;
  color: string;
}

const TabBarIcon = ({ focused, name, color }: TabBarIconProps) => (
  <View style={styles.iconWrapper}>
    <Icon name={name} size={focused ? 26 : 22} color={color} />
  </View>
);

export default function MainTabNavigator() {
  const { colors } = useTheme();

  return (
    <Tab.Navigator
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: colors.textMuted,
        tabBarStyle: [styles.tabBar, { backgroundColor: colors.white, borderTopColor: colors.border }],
        tabBarLabelStyle: styles.tabBarLabel,
        tabBarHideOnKeyboard: true,
      }}
    >
      <Tab.Screen
        name="DashboardTab"
        component={DashboardScreen}
        options={{
          tabBarLabel: 'Dashboard',
          tabBarIcon: ({ focused, color }) => (
            <TabBarIcon focused={focused} name="view-dashboard" color={color} />
          ),
        }}
      />
      <Tab.Screen
        name="SuratMasukTab"
        component={SuratMasukNavigator}
        options={{
          tabBarLabel: 'Surat',
          tabBarIcon: ({ focused, color }) => (
            <TabBarIcon focused={focused} name="email-arrow-left" color={color} />
          ),
        }}
      />
      <Tab.Screen
        name="DisposisiTab"
        component={DisposisiNavigator}
        options={{
          tabBarLabel: 'Disposisi',
          tabBarIcon: ({ focused, color }) => (
            <TabBarIcon focused={focused} name="file-document-edit" color={color} />
          ),
        }}
      />
      <Tab.Screen
        name="NotifikasiTab"
        component={NotifikasiScreen}
        options={{
          tabBarLabel: 'Notifikasi',
          tabBarIcon: ({ focused, color }) => (
            <TabBarIcon focused={focused} name="bell" color={color} />
          ),
        }}
      />
      <Tab.Screen
        name="ProfilTab"
        component={ProfilScreen}
        options={{
          tabBarLabel: 'Profil',
          tabBarIcon: ({ focused, color }) => (
            <TabBarIcon focused={focused} name="account-circle" color={color} />
          ),
        }}
      />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  tabBar: {
    height: 60,
    paddingBottom: 6,
    paddingTop: 6,
    elevation: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -2 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
  },
  tabBarLabel: {
    fontSize: 10,
    fontWeight: '600',
    marginTop: 2,
  },
  iconWrapper: {
    width: 40,
    height: 28,
    justifyContent: 'center',
    alignItems: 'center',
  },
});
